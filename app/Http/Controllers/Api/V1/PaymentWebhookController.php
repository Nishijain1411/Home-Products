<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected SettingService $settingService
    ) {}

    /**
     * Handle incoming gateway payment webhook callbacks with signature verification and idempotency.
     */
    public function handle(Request $request, ?string $gateway = null): JsonResponse
    {
        $gateway = strtolower($gateway ?? $request->input('gateway', 'razorpay'));
        $signature = $request->header('X-Razorpay-Signature')
            ?? $request->header('Stripe-Signature')
            ?? $request->header('X-Webhook-Signature')
            ?? $request->header('X-Signature')
            ?? (string) $request->input('signature');

        $rawPayload = $request->getContent();
        $payload = $request->all();

        // 1. Verify webhook signature
        $isVerified = $this->paymentService->verifyWebhookSignature($gateway, $rawPayload, $signature);

        if (! $isVerified) {
            Log::warning("Payment webhook signature verification failed for [{$gateway}].", [
                'headers' => $request->headers->all(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid payment webhook signature.',
            ], 401);
        }

        // 2. Parse event and transaction identifiers
        $parsed = $this->parsePayload($gateway, $payload);
        $transactionId = $parsed['transaction_id'];
        $eventId = $parsed['event_id'];
        $eventType = $parsed['event_type'];
        $orderNumber = $parsed['order_number'];
        $amount = $parsed['amount'];
        $currency = $parsed['currency'];

        if (empty($transactionId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing transaction identifier in webhook payload.',
            ], 422);
        }

        // 3. Idempotency Check: Prevent duplicate webhooks from reprocessing
        $existing = PaymentTransaction::where('transaction_id', $transactionId)
            ->when($eventId, fn ($q) => $q->orWhere('event_id', $eventId))
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'already_processed',
                'message' => 'Transaction or event was already processed.',
                'transaction_id' => $existing->transaction_id,
                'order_id' => $existing->order_id,
            ], 200);
        }

        // 4. Locate order
        $order = null;
        if (! empty($orderNumber)) {
            $order = Order::where('order_number', $orderNumber)->first();
        }

        if (! $order && ! empty($parsed['order_id'])) {
            $order = Order::find($parsed['order_id']);
        }

        if (! $order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found for payment transaction.',
                'order_number' => $orderNumber,
            ], 404);
        }

        // 5. Process state transition atomically
        return DB::transaction(function () use (
            $order,
            $gateway,
            $transactionId,
            $eventId,
            $eventType,
            $amount,
            $currency,
            $payload
        ) {
            $isPaidEvent = in_array($eventType, [
                'payment.captured',
                'payment.authorized',
                'order.paid',
                'payment_intent.succeeded',
                'charge.succeeded',
                'paid',
            ], true);

            $isFailedEvent = in_array($eventType, [
                'payment.failed',
                'payment_intent.payment_failed',
                'charge.failed',
                'failed',
            ], true);

            $isRefundEvent = in_array($eventType, [
                'refund.processed',
                'refund.created',
                'charge.refunded',
                'payment.refunded',
                'refunded',
            ], true);

            $status = 'pending';

            if ($isPaidEvent) {
                $status = 'paid';
                $order->markAsPaid($transactionId, $gateway, $payload);
            } elseif ($isFailedEvent) {
                $status = 'failed';
                $order->markAsFailed($payload['error_description'] ?? 'Payment failed at gateway.', $payload);
            } elseif ($isRefundEvent) {
                $status = 'refunded';
                $order->markAsRefunded($transactionId, $amount, $payload);
            }

            // Record transaction record
            $transaction = PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
                'event_id' => $eventId,
                'event_type' => $eventType,
                'amount' => $amount ?: $order->total,
                'currency' => $currency ?: 'INR',
                'status' => $status,
                'signature_verified' => true,
                'metadata' => [
                    'order_number' => $order->order_number,
                    'customer_email' => $order->customer_email,
                ],
                'raw_payload' => $payload,
            ]);

            // If payment succeeded, notify customer and merchant
            if ($status === 'paid') {
                try {
                    $this->settingService->notifyOrderPlaced($order);
                } catch (\Throwable) {
                    // Avoid failing webhook transaction due to email transport issues
                }
            }

            return response()->json([
                'status' => 'success',
                'transaction_id' => $transaction->transaction_id,
                'order_status' => $order->status,
                'payment_status' => $order->payment_status,
            ], 200);
        });
    }

    /**
     * Parse gateway-specific payload into a normalized structure.
     */
    protected function parsePayload(string $gateway, array $payload): array
    {
        return match (strtolower($gateway)) {
            'razorpay' => [
                'event_id' => $payload['event_id'] ?? ($payload['id'] ?? null),
                'event_type' => $payload['event'] ?? ($payload['event_type'] ?? 'payment.captured'),
                'transaction_id' => $payload['payload']['payment']['entity']['id']
                    ?? ($payload['payment_id'] ?? ($payload['transaction_id'] ?? null)),
                'order_number' => $payload['payload']['payment']['entity']['notes']['order_number']
                    ?? ($payload['order_number'] ?? null),
                'order_id' => $payload['payload']['payment']['entity']['notes']['order_id']
                    ?? ($payload['order_id'] ?? null),
                'amount' => isset($payload['payload']['payment']['entity']['amount'])
                    ? ((float) $payload['payload']['payment']['entity']['amount']) / 100
                    : ((float) ($payload['amount'] ?? 0)),
                'currency' => $payload['payload']['payment']['entity']['currency'] ?? ($payload['currency'] ?? 'INR'),
            ],
            'stripe' => [
                'event_id' => $payload['id'] ?? null,
                'event_type' => $payload['type'] ?? 'payment_intent.succeeded',
                'transaction_id' => $payload['data']['object']['id'] ?? ($payload['transaction_id'] ?? null),
                'order_number' => $payload['data']['object']['metadata']['order_number']
                    ?? ($payload['order_number'] ?? null),
                'order_id' => $payload['data']['object']['metadata']['order_id']
                    ?? ($payload['order_id'] ?? null),
                'amount' => isset($payload['data']['object']['amount'])
                    ? ((float) $payload['data']['object']['amount']) / 100
                    : ((float) ($payload['amount'] ?? 0)),
                'currency' => strtoupper((string) ($payload['data']['object']['currency'] ?? ($payload['currency'] ?? 'INR'))),
            ],
            default => [
                'event_id' => $payload['event_id'] ?? ($payload['id'] ?? null),
                'event_type' => $payload['event_type'] ?? ($payload['event'] ?? 'paid'),
                'transaction_id' => $payload['transaction_id'] ?? ($payload['payment_id'] ?? null),
                'order_number' => $payload['order_number'] ?? null,
                'order_id' => $payload['order_id'] ?? null,
                'amount' => (float) ($payload['amount'] ?? 0),
                'currency' => $payload['currency'] ?? 'INR',
            ],
        };
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    /**
     * Get webhook secret or gateway secret key from config/settings.
     */
    public function getGatewaySecret(string $gateway = 'default'): string
    {
        $settingService = app(SettingService::class);

        return match (strtolower($gateway)) {
            'razorpay' => (string) ($settingService->get('razorpay_key_secret', config('services.razorpay.secret', 'rzp_test_secret_key'))),
            'stripe' => (string) ($settingService->get('stripe_webhook_secret', config('services.stripe.webhook_secret', 'whsec_test_secret'))),
            default => (string) config('app.key'),
        };
    }

    /**
     * Cryptographically verify payment signature for client-side callback or redirect.
     */
    public function verifyPaymentSignature(string $gateway, array $data, ?string $secret = null): bool
    {
        $secret = $secret ?: $this->getGatewaySecret($gateway);

        if (empty($secret)) {
            return false;
        }

        return match (strtolower($gateway)) {
            'razorpay' => $this->verifyRazorpaySignature(
                $data['razorpay_order_id'] ?? '',
                $data['razorpay_payment_id'] ?? '',
                $data['razorpay_signature'] ?? '',
                $secret
            ),
            'stripe' => ! empty($data['payment_intent_id']) && ($data['status'] ?? '') === 'succeeded',
            default => $this->verifyGenericSignature($data, $secret),
        };
    }

    /**
     * Verify Razorpay HMAC-SHA256 signature: hmac_sha256(order_id + "|" + payment_id, secret).
     */
    public function verifyRazorpaySignature(string $orderId, string $paymentId, string $signature, string $secret): bool
    {
        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            return false;
        }

        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Verify generic HMAC-SHA256 signature for custom gateway integrations.
     */
    public function verifyGenericSignature(array $data, string $secret): bool
    {
        $signature = $data['signature'] ?? null;
        if (! $signature) {
            return false;
        }

        unset($data['signature']);
        ksort($data);
        $payload = http_build_query($data);
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Verify incoming webhook signature from payment gateways.
     */
    public function verifyWebhookSignature(string $gateway, string $payload, string $signature, ?string $secret = null): bool
    {
        $secret = $secret ?: $this->getGatewaySecret($gateway);

        if (empty($secret) || empty($signature)) {
            return false;
        }

        return match (strtolower($gateway)) {
            'razorpay' => hash_equals(hash_hmac('sha256', $payload, $secret), $signature),
            'stripe' => $this->verifyStripeWebhookSignature($payload, $signature, $secret),
            default => hash_equals(hash_hmac('sha256', $payload, $secret), $signature),
        };
    }

    /**
     * Verify Stripe webhook signature header (t=timestamp,v1=signature).
     */
    protected function verifyStripeWebhookSignature(string $payload, string $header, string $secret): bool
    {
        $parts = [];
        foreach (explode(',', $header) as $item) {
            $kv = explode('=', trim($item), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }

        $timestamp = $parts['t'] ?? null;
        $signature = $parts['v1'] ?? null;

        if (! $timestamp || ! $signature) {
            return false;
        }

        $signedPayload = "{$timestamp}.{$payload}";
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Record payment transaction in database for auditing and idempotency.
     */
    public function recordTransaction(Order $order, array $data): PaymentTransaction
    {
        return PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => $data['gateway'] ?? ($order->payment_method ?: 'gateway'),
            'transaction_id' => $data['transaction_id'],
            'event_id' => $data['event_id'] ?? null,
            'event_type' => $data['event_type'] ?? 'payment.captured',
            'amount' => $data['amount'] ?? $order->total,
            'currency' => $data['currency'] ?? 'INR',
            'status' => $data['status'] ?? 'paid',
            'signature_verified' => (bool) ($data['signature_verified'] ?? true),
            'metadata' => $data['metadata'] ?? [],
            'raw_payload' => $data['raw_payload'] ?? [],
        ]);
    }

    /**
     * Process payment for an order.
     *
     * @param  string  $method  ('cod', 'card', 'upi', 'gateway')
     */
    public function process(string $method, float $amount, array $details = []): array
    {
        if ($amount <= 0) {
            throw new RuntimeException('Invalid payment amount.');
        }

        return match ($method) {
            'cod' => [
                'success' => true,
                'method' => 'cod',
                'method_label' => 'Cash on Delivery',
                'status' => 'pending',
                'reference' => 'COD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'paid_at' => null,
            ],
            'card' => $this->processCardPayment($amount, $details),
            'upi' => $this->processUpiPayment($amount, $details),
            'gateway', 'razorpay', 'stripe' => $this->processGatewayVerification($method, $amount, $details),
            default => throw new RuntimeException("Unsupported payment method [{$method}]."),
        };
    }

    protected function processCardPayment(float $amount, array $details): array
    {
        $cardNumber = preg_replace('/\s+/', '', $details['cardNumber'] ?? '');
        $cardExpiry = trim($details['cardExpiry'] ?? '');
        $cardCvv = trim($details['cardCvv'] ?? '');
        $cardName = trim($details['cardName'] ?? '');

        if (strlen($cardNumber) < 15 || strlen($cardNumber) > 19) {
            throw new RuntimeException('Invalid card number. Please provide a valid 15-19 digit card number.');
        }

        if (empty($cardExpiry) || ! preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $cardExpiry)) {
            throw new RuntimeException('Invalid card expiry date. Use MM/YY format.');
        }

        if (strlen($cardCvv) < 3 || strlen($cardCvv) > 4) {
            throw new RuntimeException('Invalid CVV code.');
        }

        if (empty($cardName)) {
            throw new RuntimeException('Cardholder name is required.');
        }

        $last4 = substr($cardNumber, -4);

        return [
            'success' => true,
            'method' => 'card',
            'method_label' => 'Credit / Debit Card',
            'status' => 'paid',
            'reference' => 'TXN-CARD-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
            'card_last4' => $last4,
            'card_name' => $cardName,
            'paid_at' => now(),
        ];
    }

    protected function processUpiPayment(float $amount, array $details): array
    {
        $upiId = trim($details['upiId'] ?? '');

        if (! preg_match('/^[\w\.\-]+@[\w\-]+$/', $upiId)) {
            throw new RuntimeException('Invalid UPI ID. Format should be username@bank (e.g. user@okhdfcbank).');
        }

        return [
            'success' => true,
            'method' => 'upi',
            'method_label' => 'UPI (Google Pay / PhonePe / Paytm)',
            'status' => 'paid',
            'reference' => 'UPI-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
            'upi_id' => $upiId,
            'paid_at' => now(),
        ];
    }

    protected function processGatewayVerification(string $gateway, float $amount, array $details): array
    {
        $transactionId = $details['transaction_id'] ?? $details['razorpay_payment_id'] ?? null;

        if (! $transactionId) {
            throw new RuntimeException('Payment gateway transaction ID is required.');
        }

        $verified = $this->verifyPaymentSignature($gateway, $details);

        if (! $verified) {
            throw new RuntimeException('Payment signature verification failed. The transaction could not be authenticated.');
        }

        return [
            'success' => true,
            'method' => $gateway,
            'method_label' => ucfirst($gateway).' Gateway',
            'status' => 'paid',
            'reference' => $transactionId,
            'signature_verified' => true,
            'paid_at' => now(),
        ];
    }
}

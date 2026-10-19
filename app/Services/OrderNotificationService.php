<?php

namespace App\Services;

use App\Mail\OrderCancelledMail;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderDeliveredMail;
use App\Mail\OrderRefundedMail;
use App\Mail\OrderShippedMail;
use App\Mail\PaymentConfirmedMail;
use App\Models\Order;
use App\Models\OrderNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Dispatch order placed notifications (customer confirmation and admin alert).
     */
    public function notifyOrderPlaced(Order $order): bool
    {
        $sent = $this->sendNotification($order, 'order_placed', function ($storeName) use ($order) {
            return new OrderConfirmationMail($order, $storeName);
        });

        // Admin new order alert
        $notifyNewOrder = (bool) $this->settingService->get('notify_new_order', true);
        $adminEmail = (string) $this->settingService->get('admin_notification_email', $this->settingService->get('contact_email', ''));

        if ($notifyNewOrder && ! empty($adminEmail)) {
            try {
                $this->settingService->applyMailConfig();
                $storeName = (string) $this->settingService->get('store_name', 'Custom Commerce');
                $fromAddress = (string) $this->settingService->get('mail_from_address', config('mail.from.address', 'noreply@customcommerce.test'));
                $fromName = (string) $this->settingService->get('mail_from_name', $storeName);
                $totalFormatted = format_price($order->total);

                Mail::raw(
                    "A new customer order has been placed on {$storeName}!\n\nOrder Number: {$order->order_number}\nCustomer: {$order->customer_first_name} {$order->customer_last_name} ({$order->customer_email})\nTotal: {$totalFormatted}\nPayment: {$order->payment_method_label}\n\nView details in your admin dashboard.",
                    function ($message) use ($adminEmail, $order, $storeName, $fromAddress, $fromName) {
                        $message->to($adminEmail)
                            ->from($fromAddress, $fromName)
                            ->subject("[{$storeName}] New Order Alert - #{$order->order_number}");
                    }
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to send admin order alert: '.$e->getMessage());
            }
        }

        return $sent;
    }

    /**
     * Dispatch payment confirmation email when order payment is marked paid.
     */
    public function notifyPaymentConfirmed(Order $order): bool
    {
        return $this->sendNotification($order, 'payment_confirmed', function ($storeName) use ($order) {
            return new PaymentConfirmedMail($order, $storeName);
        });
    }

    /**
     * Dispatch shipping notification email when order is shipped.
     */
    public function notifyOrderShipped(Order $order): bool
    {
        return $this->sendNotification($order, 'order_shipped', function ($storeName) use ($order) {
            return new OrderShippedMail($order, $storeName);
        });
    }

    /**
     * Dispatch delivery confirmation email when order is delivered.
     */
    public function notifyOrderDelivered(Order $order): bool
    {
        return $this->sendNotification($order, 'order_delivered', function ($storeName) use ($order) {
            return new OrderDeliveredMail($order, $storeName);
        });
    }

    /**
     * Dispatch order cancellation email.
     */
    public function notifyOrderCancelled(Order $order, ?string $reason = null): bool
    {
        return $this->sendNotification($order, 'order_cancelled', function ($storeName) use ($order, $reason) {
            return new OrderCancelledMail($order, $storeName, $reason);
        });
    }

    /**
     * Dispatch refund confirmation email.
     */
    public function notifyOrderRefunded(Order $order, ?float $amount = null): bool
    {
        return $this->sendNotification($order, 'order_refunded', function ($storeName) use ($order, $amount) {
            return new OrderRefundedMail($order, $storeName, $amount);
        });
    }

    /**
     * Core idempotent email sender: prevents sending duplicate notifications.
     */
    protected function sendNotification(Order $order, string $type, callable $mailableFactory): bool
    {
        if (empty($order->customer_email)) {
            return false;
        }

        // 1. Deduplication check: Do not re-send if already recorded
        $alreadySent = OrderNotification::where('order_id', $order->id)
            ->where('type', $type)
            ->exists();

        if ($alreadySent) {
            return false;
        }

        // 2. Atomically record notification entry to prevent concurrent race condition duplicates
        try {
            $recorded = DB::transaction(function () use ($order, $type) {
                return OrderNotification::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'type' => $type,
                    ],
                    [
                        'recipient_email' => $order->customer_email,
                        'sent_at' => now(),
                    ]
                );
            });

            // If it already existed before this transaction (firstOrCreate did not create it newly)
            if (! $recorded->wasRecentlyCreated) {
                return false;
            }
        } catch (\Throwable) {
            return false;
        }

        // 3. Dispatch the queued mailable
        try {
            $this->settingService->applyMailConfig();
            $storeName = (string) $this->settingService->get('store_name', 'Custom Commerce');
            $mailable = $mailableFactory($storeName);

            Mail::to($order->customer_email)->send($mailable);

            return true;
        } catch (\Throwable $e) {
            Log::warning("Failed to dispatch [{$type}] notification for Order #{$order->order_number}: ".$e->getMessage());

            return false;
        }
    }
}

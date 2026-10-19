<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        public Order $order,
        public string $storeName = 'Custom Commerce'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->storeName}] Order Confirmation - #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        $paymentStatusLabel = match ($this->order->payment_status) {
            'paid' => 'Paid (Payment verified)',
            'failed' => 'Payment Failed',
            'refunded' => 'Refunded',
            default => 'Pending (Cash on Delivery)',
        };

        $orderStatusLabel = ucfirst((string) $this->order->status);
        $totalFormatted = format_price($this->order->total);

        return new Content(
            htmlString: nl2br(e(
                "Hello {$this->order->customer_first_name},\n\nThank you for ordering with {$this->storeName}!\n\nOrder Number: {$this->order->order_number}\nOrder Total: {$totalFormatted}\nPayment Method: {$this->order->payment_method_label}\nPayment Status: {$paymentStatusLabel}\nOrder Status: {$orderStatusLabel}\n\nWe will notify you once your order has been dispatched."
            )),
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

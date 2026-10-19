<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmedMail extends Mailable implements ShouldQueue
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
            subject: "[{$this->storeName}] Payment Confirmed - Order #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        $totalFormatted = format_price($this->order->total);
        $reference = $this->order->payment_reference ?: 'N/A';

        return new Content(
            htmlString: nl2br(e(
                "Hello {$this->order->customer_first_name},\n\nWe have successfully received and verified your payment for Order #{$this->order->order_number}!\n\nAmount Paid: {$totalFormatted}\nPayment Method: {$this->order->payment_method_label}\nTransaction Reference: {$reference}\n\nOur fulfillment team is now preparing your items. You will receive an email as soon as your package has been shipped.\n\nThank you for shopping with {$this->storeName}!"
            )),
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

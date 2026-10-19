<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShippedMail extends Mailable implements ShouldQueue
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
            subject: "[{$this->storeName}] Order Shipped - #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        $shipping = $this->order->shipping_address ?? [];
        $addressStr = implode(', ', array_filter([
            $shipping['address'] ?? '',
            $shipping['city'] ?? '',
            $shipping['state'] ?? '',
            $shipping['postal_code'] ?? '',
            $shipping['country'] ?? '',
        ]));

        return new Content(
            htmlString: nl2br(e(
                "Hello {$this->order->customer_first_name},\n\nGreat news! Your Order #{$this->order->order_number} has been shipped and is on its way to you.\n\nDelivery Destination:\n{$addressStr}\n\nWe will notify you once the courier confirms delivery.\n\nThank you for choosing {$this->storeName}!"
            )),
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

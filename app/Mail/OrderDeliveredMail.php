<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredMail extends Mailable implements ShouldQueue
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
            subject: "[{$this->storeName}] Order Delivered - #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: nl2br(e(
                "Hello {$this->order->customer_first_name},\n\nYour Order #{$this->order->order_number} has been delivered!\n\nWe hope you love your new purchase. If you have any questions or feedback about your items, please do not hesitate to contact our customer support.\n\nThank you for shopping with {$this->storeName}!"
            )),
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

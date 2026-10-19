<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        public Order $order,
        public string $storeName = 'Custom Commerce',
        public ?string $reason = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->storeName}] Order Cancelled - #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        $reasonText = $this->reason ? "\nReason: {$this->reason}\n" : '';

        return new Content(
            htmlString: nl2br(e(
                "Hello {$this->order->customer_first_name},\n\nYour Order #{$this->order->order_number} has been cancelled.{$reasonText}\nIf any online payment was completed for this order, a full refund will be credited to your original payment method.\n\nIf you have any questions, please reach out to {$this->storeName} customer support."
            )),
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

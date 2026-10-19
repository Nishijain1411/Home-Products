<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderRefundedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        public Order $order,
        public string $storeName = 'Custom Commerce',
        public ?float $refundAmount = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->storeName}] Refund Processed - Order #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        $amountFormatted = format_price($this->refundAmount ?: (float) $this->order->total);

        return new Content(
            htmlString: nl2br(e(
                "Hello {$this->order->customer_first_name},\n\nA refund of {$amountFormatted} has been processed for Order #{$this->order->order_number}.\n\nThe funds should reflect in your original payment account within 5 to 7 business days, depending on your card issuer or banking provider.\n\nThank you for choosing {$this->storeName}."
            )),
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

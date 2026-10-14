<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    /**
     * Process payment for an order.
     *
     * @param  string  $method  ('cod', 'card', 'upi')
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
}

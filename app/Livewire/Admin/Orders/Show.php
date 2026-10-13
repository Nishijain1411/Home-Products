<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    public Order $order;

    public string $status = '';

    private const ALLOWED_TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    public function mount(string $orderNumber): void
    {
        $this->order = Order::with([
            'customer',
            'items.product',
            'items.variant',
        ])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $this->status = $this->order->status;
    }

    public function updateStatus(): void
    {
        $this->validate([
            'status' => [
                'required',
                'in:pending,processing,shipped,delivered,cancelled',
            ],
        ]);

        $this->order->refresh();

        $currentStatus = $this->order->status;

        if ($this->status === $currentStatus) {
            session()->flash('success', 'Order status is unchanged.');

            return;
        }

        $allowedTransitions = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        if (! in_array($this->status, $allowedTransitions, true)) {
            $this->status = $currentStatus;

            throw ValidationException::withMessages([
                'status' => "Cannot change an order from {$currentStatus} to the requested status.",
            ]);
        }

        $this->order->update([
            'status' => $this->status,
        ]);

        $this->order->refresh();

        session()->flash(
            'success',
            'Order status updated successfully.'
        );
    }

    public function render()
    {
        return view('livewire.admin.orders.show')
            ->layout('layouts.app');
    }
}

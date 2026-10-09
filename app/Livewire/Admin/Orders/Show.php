<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Livewire\Component;

class Show extends Component
{
    public Order $order;

    public string $status = '';

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
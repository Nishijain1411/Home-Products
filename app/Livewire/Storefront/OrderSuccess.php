<?php

namespace App\Livewire\Storefront;

use App\Models\Order;
use Livewire\Component;

class OrderSuccess extends Component
{
    public Order $order;

    public function mount(string $orderNumber): void
    {
        $this->order = Order::with('items')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // Server-side authorization check: Prevent unauthorized access to other customers' orders
        if (auth()->check() && ! auth()->user()->canAccessAdmin() && $this->order->customer_id) {
            if (auth()->id() !== $this->order->customer_id) {
                abort(403, 'Unauthorized access to order.');
            }
        }

        // CRITICAL SECURITY RULE: Never mark an order as paid simply because a customer loads the success page.
    }

    public function render()
    {
        return view('livewire.storefront.order-success')->layout('storefront.layouts.app');
    }
}

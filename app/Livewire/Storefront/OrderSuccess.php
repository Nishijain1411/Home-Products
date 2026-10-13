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
    }

    public function render()
    {
        return view('livewire.storefront.order-success')->layout('storefront.layouts.app');
    }
}

<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use App\Models\Order;


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

<?php

namespace App\Livewire\Storefront\Orders;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Order $order;

    public function mount(string $orderNumber): void
    {
        $this->order = Order::query()
            ->where('order_number', $orderNumber)
            ->where('customer_id', Auth::id())
            ->with(['items.product', 'items.variant'])
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.storefront.orders.show')
            ->layout('storefront.layouts.app');
    }
}

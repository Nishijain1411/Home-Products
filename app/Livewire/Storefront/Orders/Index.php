<?php

namespace App\Livewire\Storefront\Orders;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render()
    {
        $orders = Order::query()
            ->where('customer_id', Auth::id())
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('livewire.storefront.orders.index', [
            'orders' => $orders,
        ])->layout('layouts.app');
    }
}

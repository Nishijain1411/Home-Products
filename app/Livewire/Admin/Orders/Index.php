<?php

namespace App\Livewire\Admin\Orders;

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

        return view('livewire.admin.orders.index', [
            'orders' => $orders,
        ])->layout('layouts.app');
    }
}

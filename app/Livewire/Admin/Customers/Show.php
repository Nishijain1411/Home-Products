<?php

namespace App\Livewire\Admin\Customers;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public User $customer;

    public function mount(int $customerId): void
    {
        $this->customer = User::withCount('orders')
            ->withSum('orders', 'total')
            ->findOrFail($customerId);
    }

    public function render()
    {
        $orders = $this->customer
            ->orders()
            ->latest()
            ->paginate(10);

        return view('livewire.admin.customers.show', [
            'orders' => $orders,
        ])->layout('layouts.app');
    }
}
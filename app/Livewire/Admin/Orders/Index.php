<?php

namespace App\Livewire\Admin\Orders;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $paymentStatus = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentStatus(): void
    {
        $this->resetPage();
    }
    public function render()
    {
        $orders = Order::query()
            ->with('customer')
            ->withCount('items')
            ->when(
                $this->search,
                function ($query) {
                    $query->where(function ($query) {
                        $query
                            ->where('order_number', 'like', '%' . $this->search . '%')
                            ->orWhereHas('customer', function ($customerQuery) {
                                $customerQuery
                                    ->where('name', 'like', '%' . $this->search . '%')
                                    ->orWhere('email', 'like', '%' . $this->search . '%');
                            });
                    });
                }
            )
            ->when(
                $this->status !== '',
                fn ($query) => $query->where('status', $this->status)
            )
            ->when(
                $this->paymentStatus !== '',
                fn ($query) => $query->where(
                    'payment_status',
                    $this->paymentStatus
                )
            )
            ->latest()
            ->paginate(15);

        return view('livewire.admin.orders.index', [
            'orders' => $orders,
        ])->layout('layouts.app');
    }
}

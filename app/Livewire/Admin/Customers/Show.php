<?php

namespace App\Livewire\Admin\Customers;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public User $customer;

    public string $dateFrom = '';

    public string $dateTo = '';

    protected $queryString = [
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

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
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.customers.show', [
            'orders' => $orders,
        ])->layout('layouts.app');
    }
}

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

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $datePreset = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'paymentStatus' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'datePreset' => ['except' => ''],
    ];

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

    public function updatedDateFrom(): void
    {
        $this->datePreset = '';
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->datePreset = '';
        $this->resetPage();
    }

    public function updatedDatePreset($value): void
    {
        $now = now();

        switch ($value) {
            case 'today':
                $this->dateFrom = $now->copy()->startOfDay()->format('Y-m-d');
                $this->dateTo = $now->copy()->endOfDay()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->dateFrom = $now->copy()->subDay()->startOfDay()->format('Y-m-d');
                $this->dateTo = $now->copy()->subDay()->endOfDay()->format('Y-m-d');
                break;
            case 'last_7_days':
                $this->dateFrom = $now->copy()->subDays(6)->startOfDay()->format('Y-m-d');
                $this->dateTo = $now->copy()->endOfDay()->format('Y-m-d');
                break;
            case 'last_30_days':
                $this->dateFrom = $now->copy()->subDays(29)->startOfDay()->format('Y-m-d');
                $this->dateTo = $now->copy()->endOfDay()->format('Y-m-d');
                break;
            case 'this_month':
                $this->dateFrom = $now->copy()->startOfMonth()->format('Y-m-d');
                $this->dateTo = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->dateFrom = $now->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->dateTo = $now->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            default:
                $this->dateFrom = '';
                $this->dateTo = '';
                break;
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'status',
            'paymentStatus',
            'dateFrom',
            'dateTo',
            'datePreset',
        ]);

        $this->resetPage();
    }

    public function render()
    {
        $orders = Order::query()
            ->with(['customer', 'items'])
            ->withCount('items')
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($q) use ($term) {
                    $q->where('order_number', 'like', $term)
                        ->orWhereHas('customer', function ($cq) use ($term) {
                            $cq->where('name', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        })
                        ->orWhere('shipping_address', 'like', $term)
                        ->orWhere('billing_address', 'like', $term);
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->paymentStatus !== '', function ($query) {
                $query->where('payment_status', $this->paymentStatus);
            })
            ->when($this->dateFrom !== '', function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo !== '', function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->latest('id')
            ->paginate(10);

        return view('livewire.admin.orders.index', [
            'orders' => $orders,
        ])->layout('layouts.app');
    }
}

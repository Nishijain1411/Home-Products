<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    public Order $order;

    public string $status = '';

    public string $historyDateFrom = '';

    public string $historyDateTo = '';

    private const ALLOWED_TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    public function clearHistoryFilters(): void
    {
        $this->historyDateFrom = '';
        $this->historyDateTo = '';
    }

    public function mount(string $orderNumber): void
    {
        $this->order = Order::with([
            'customer',
            'items.product',
            'items.variant',
        ])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $this->status = $this->order->status;
    }

    public function updateStatus(): void
    {
        $this->validate([
            'status' => [
                'required',
                'in:pending,processing,shipped,delivered,cancelled',
            ],
        ]);

        $this->order->refresh();

        $currentStatus = $this->order->status;

        if ($this->status === $currentStatus) {
            session()->flash('success', 'Order status is unchanged.');

            return;
        }

        if (! $this->order->canTransitionTo($this->status)) {
            $this->status = $currentStatus;

            throw ValidationException::withMessages([
                'status' => "Cannot change an order from {$currentStatus} to the requested status.",
            ]);
        }

        try {
            $this->order->transitionTo($this->status, 'Admin updated status via dashboard', Auth::user());
        } catch (\DomainException) {
            $this->status = $currentStatus;

            throw ValidationException::withMessages([
                'status' => "Cannot change an order from {$currentStatus} to the requested status.",
            ]);
        }

        $this->order->refresh();

        session()->flash(
            'success',
            'Order status updated successfully.'
        );
    }

    public function render()
    {
        $relatedOrders = collect();

        $customerEmail = $this->order->customer?->email ?? ($this->order->shipping_address['email'] ?? null);

        if ($this->order->customer_id || $customerEmail) {
            $relatedOrders = Order::query()
                ->where('id', '!=', $this->order->id)
                ->where(function ($q) use ($customerEmail) {
                    if ($this->order->customer_id) {
                        $q->where('customer_id', $this->order->customer_id);
                    }
                    if ($customerEmail) {
                        $q->orWhere('shipping_address', 'like', '%'.$customerEmail.'%');
                    }
                })
                ->when($this->historyDateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->historyDateFrom))
                ->when($this->historyDateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->historyDateTo))
                ->latest('id')
                ->take(10)
                ->get();
        }

        return view('livewire.admin.orders.show', [
            'relatedOrders' => $relatedOrders,
        ])->layout('layouts.app');
    }
}

<?php

namespace App\Livewire\Admin\Inventory;

use App\Models\InventoryAdjustment;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $reasonFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedReasonFilter(): void
    {
        $this->resetPage();
    }

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
        $this->reset([
            'search',
            'typeFilter',
            'reasonFilter',
            'dateFrom',
            'dateTo',
        ]);

        $this->resetPage();
    }

    public function render()
    {
        $adjustments = InventoryAdjustment::query()
            ->with([
                'inventory.variant.product',
                'inventory.variant.optionValues',
                'user',
            ])
            ->when($this->search !== '', function ($query) {
                $search = '%'.$this->search.'%';

                $query->where(function ($q) use ($search) {
                    $q->where('reason', 'like', $search)
                        ->orWhere('notes', 'like', $search)
                        ->orWhereHas(
                            'inventory.variant',
                            fn ($variant) => $variant
                                ->where('sku', 'like', $search)
                                ->orWhereHas(
                                    'product',
                                    fn ($product) => $product
                                        ->where('name', 'like', $search)
                                )
                        )
                        ->orWhereHas(
                            'user',
                            fn ($user) => $user
                                ->where('name', 'like', $search)
                        );
                });
            })
            ->when(
                $this->typeFilter !== '',
                fn ($query) => $query->where(
                    'type',
                    $this->typeFilter
                )
            )
            ->when(
                $this->reasonFilter !== '',
                fn ($query) => $query->where(
                    'reason',
                    $this->reasonFilter
                )
            )
            ->when(
                $this->dateFrom !== '',
                fn ($query) => $query->whereDate(
                    'created_at',
                    '>=',
                    $this->dateFrom
                )
            )
            ->when(
                $this->dateTo !== '',
                fn ($query) => $query->whereDate(
                    'created_at',
                    '<=',
                    $this->dateTo
                )
            )
            ->latest('id')
            ->paginate(15);

        return view(
            'livewire.admin.inventory.history',
            ['adjustments' => $adjustments]
        )->layout('layouts.app');
    }
}

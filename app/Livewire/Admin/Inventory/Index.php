<?php

namespace App\Livewire\Admin\Inventory;

use App\Models\Inventory;
use App\Models\InventoryAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $stockFilter = '';

    public bool $showAdjustmentModal = false;

    public ?int $selectedInventoryId = null;

    public string $adjustmentType = 'increase';

    public int $adjustmentQuantity = 1;

    public string $adjustmentReason = 'new_stock';

    public string $adjustmentNotes = '';

    public function openAdjustment(int $inventoryId): void
    {
        $this->resetValidation();

        $inventory = Inventory::findOrFail($inventoryId);

        $this->selectedInventoryId = $inventory->id;
        $this->adjustmentType = 'increase';
        $this->adjustmentQuantity = 1;
        $this->adjustmentReason = 'new_stock';
        $this->adjustmentNotes = '';
        $this->showAdjustmentModal = true;
    }

    public function closeAdjustment(): void
    {
        $this->showAdjustmentModal = false;
        $this->selectedInventoryId = null;
        $this->resetValidation();
    }

    public function saveAdjustment(): void
    {
        $validated = $this->validate([
            'selectedInventoryId' => ['required', 'integer', 'exists:inventories,id'],
            'adjustmentType' => ['required', 'in:increase,decrease'],
            'adjustmentQuantity' => ['required', 'integer', 'min:1'],
            'adjustmentReason' => [
                'required',
                'in:new_stock,damaged_goods,stock_correction,return,other',
            ],
            'adjustmentNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated) {
            $inventory = Inventory::whereKey(
                $validated['selectedInventoryId']
            )->lockForUpdate()->firstOrFail();

            $before = (int) $inventory->quantity;
            $change = (int) $validated['adjustmentQuantity'];

            $after = $validated['adjustmentType'] === 'increase'
                ? $before + $change
                : $before - $change;

            if ($after < (int) $inventory->reserved_quantity) {
                throw ValidationException::withMessages([
                    'adjustmentQuantity' => 'Stock cannot be reduced below the reserved quantity.',
                ]);
            }

            $inventory->update([
                'quantity' => $after,
            ]);

            InventoryAdjustment::create([
                'inventory_id' => $inventory->id,
                'user_id' => Auth::id(),
                'quantity_before' => $before,
                'quantity_changed' => $change,
                'quantity_after' => $after,
                'type' => $validated['adjustmentType'],
                'reason' => $validated['adjustmentReason'],
                'notes' => $validated['adjustmentNotes'] ?: null,
            ]);
        });

        $this->showAdjustmentModal = false;
        $this->selectedInventoryId = null;

        session()->flash('inventory_success', 'Stock adjusted successfully.');

        $this->resetValidation();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStockFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $inventory = Inventory::query()
            ->with([
                'variant.product',
                'variant.product.images',
                'variant.optionValues',
            ])
            ->when($this->search !== '', function ($query) {
                $query->whereHas('variant', function ($variantQuery) {
                    $variantQuery->where('sku', 'like', '%'.$this->search.'%')
                        ->orWhereHas('product', function ($productQuery) {
                            $productQuery->where(
                                'name',
                                'like',
                                '%'.$this->search.'%'
                            );
                        });
                });
            })
            ->when($this->stockFilter === 'low', function ($query) {
                $query->where('quantity', '>', 0)
                    ->where('quantity', '<=', 10);
            })
            ->when($this->stockFilter === 'out', function ($query) {
                $query->where('quantity', '<=', 0);
            })
            ->when($this->stockFilter === 'in', function ($query) {
                $query->where('quantity', '>', 10);
            })
            ->latest('id')
            ->paginate(15);

        return view('livewire.admin.inventory.index', [
            'inventory' => $inventory,
        ])->layout('layouts.app');
    }
}

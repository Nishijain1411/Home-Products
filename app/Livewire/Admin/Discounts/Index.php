<?php

namespace App\Livewire\Admin\Discounts;

use App\Models\Discount;
use App\Models\DiscountRedemption;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = 'all';

    public string $statusFilter = 'all';

    public bool $showDeleteModal = false;

    public ?int $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $discount = Discount::findOrFail($id);
        $discount->is_active = ! $discount->is_active;
        $discount->save();

        $statusLabel = $discount->is_active ? 'activated' : 'deactivated';
        session()->flash('success', "Discount '{$discount->code}' {$statusLabel} successfully.");
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            $discount = Discount::findOrFail($this->deleteId);
            $code = $discount->code;
            $discount->delete();

            $this->showDeleteModal = false;
            $this->deleteId = null;
            session()->flash('success', "Discount '{$code}' deleted successfully.");
        }
    }

    public function render()
    {
        $query = Discount::query()->withCount('redemptions');

        if ($this->search !== '') {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                });
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        } elseif ($this->statusFilter === 'expired') {
            $query->whereNotNull('expires_at')->where('expires_at', '<', now());
        }

        $discounts = $query->latest()->paginate(10);

        $totalDiscounts = Discount::count();
        $activeDiscounts = Discount::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })->count();
        $totalRedemptions = (int) Discount::sum('times_used');
        $totalDiscountGiven = (float) DiscountRedemption::sum('amount');

        return view('livewire.admin.discounts.index', [
            'discounts' => $discounts,
            'totalDiscounts' => $totalDiscounts,
            'activeDiscounts' => $activeDiscounts,
            'totalRedemptions' => $totalRedemptions,
            'totalDiscountGiven' => $totalDiscountGiven,
        ]);
    }
}

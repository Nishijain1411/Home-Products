<?php

namespace App\Livewire\Admin\Customers;

use App\Models\CustomerSegment;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $segmentFilter = '';

    public array $selected = [];

    public bool $selectAll = false;

    public bool $showBulkSegmentModal = false;

    public string $selectedSegmentId = '';

    public string $bulkMode = 'add'; // 'add' or 'move'

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'segmentFilter' => ['except' => ''],
    ];

    public function dismissAlert(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function updatedSegmentFilter(): void
    {
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'segmentFilter']);
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return ! empty($this->search) || $this->segmentFilter !== '';
    }

    public function buildFilteredCustomersQuery()
    {
        return User::query()
            ->with(['segments'])
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->segmentFilter !== '', function ($query) {
                if ($this->segmentFilter === 'unsegmented') {
                    $query->doesntHave('segments');
                } else {
                    $query->whereHas('segments', function ($q) {
                        $q->where('customer_segments.id', $this->segmentFilter);
                    });
                }
            });
    }

    public function getCurrentPageIdsProperty(): array
    {
        return $this->buildFilteredCustomersQuery()
            ->latest()
            ->paginate(15)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function updatedSelectAll(bool $value): void
    {
        $pageIds = $this->currentPageIds;

        if ($value) {
            $this->selected = array_values(array_unique(array_merge(
                array_map('strval', $this->selected),
                $pageIds
            )));
        } else {
            $this->selected = array_values(array_diff(
                array_map('strval', $this->selected),
                $pageIds
            ));
        }

        $this->syncSelectAllState();
    }

    public function updatedSelected(): void
    {
        $this->syncSelectAllState();
    }

    public function syncSelectAllState(): void
    {
        $pageIds = $this->currentPageIds;
        $selectedStrings = array_map('strval', $this->selected);

        $this->selectAll = ! empty($pageIds) && empty(array_diff($pageIds, $selectedStrings));
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function openBulkSegmentModal(): void
    {
        if (empty($this->selected)) {
            $this->errorMessage = 'Please select at least one customer first.';

            return;
        }

        $this->selectedSegmentId = '';
        $this->bulkMode = 'add';
        $this->showBulkSegmentModal = true;
    }

    public function closeBulkSegmentModal(): void
    {
        $this->showBulkSegmentModal = false;
        $this->selectedSegmentId = '';
    }

    public function bulkAssignSegment(): void
    {
        if (! auth()->check() || ! auth()->user()->canAccessAdmin()) {
            abort(403, 'Unauthorized.');
        }

        if (empty($this->selected)) {
            $this->errorMessage = 'No customers were selected.';

            return;
        }

        $this->validate([
            'selectedSegmentId' => ['required', 'integer', 'exists:customer_segments,id'],
            'bulkMode' => ['required', 'in:add,move'],
        ], [
            'selectedSegmentId.required' => 'Please select a customer segment.',
            'selectedSegmentId.exists' => 'The selected segment is invalid.',
        ]);

        $segment = CustomerSegment::findOrFail((int) $this->selectedSegmentId);
        $customerIds = array_map('intval', $this->selected);

        if ($this->bulkMode === 'move') {
            foreach ($customerIds as $cId) {
                $user = User::find($cId);
                if ($user) {
                    $user->segments()->sync([$segment->id]);
                }
            }
            $this->successMessage = 'Moved '.count($customerIds)." customer(s) exclusively into '{$segment->name}'.";
        } else {
            $segment->customers()->syncWithoutDetaching($customerIds);
            $this->successMessage = 'Added '.count($customerIds)." customer(s) to '{$segment->name}'.";
        }

        $this->selected = [];
        $this->selectAll = false;
        $this->showBulkSegmentModal = false;
        $this->selectedSegmentId = '';
    }

    public function render()
    {
        $customers = $this->buildFilteredCustomersQuery()
            ->latest()
            ->paginate(15);

        // Keep select-all state synced
        $currentPageIds = $customers->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->selectAll = ! empty($currentPageIds) && empty(array_diff($currentPageIds, array_map('strval', $this->selected)));

        $segments = CustomerSegment::where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.customers.index', [
            'customers' => $customers,
            'segments' => $segments,
        ]);
    }
}

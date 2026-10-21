<?php

namespace App\Livewire\Admin\CustomerSegments;

use App\Models\CustomerSegment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Segment list filtering
    public string $searchSegment = '';

    // Active Segment selected for inspection & member management
    public ?int $activeSegmentId = null;

    public string $searchMembers = '';

    public array $selectedMembers = [];

    public bool $selectAllMembers = false;

    // Segment create/edit modal
    public bool $showSegmentModal = false;

    public ?int $editingSegmentId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public string $color = 'blue';

    public bool $is_active = true;

    // Segment deletion modal
    public bool $showDeleteModal = false;

    public ?int $deleteSegmentId = null;

    // Add Customers to Segment Modal
    public bool $showAddCustomersModal = false;

    public string $customerSearch = '';

    public array $selectedCustomers = [];

    public bool $selectAllCustomers = false;

    public string $assignmentMode = 'add'; // 'add' or 'move'

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    protected $queryString = [
        'searchSegment' => ['except' => ''],
        'activeSegmentId' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        if (! auth()->check() || ! auth()->user()->canAccessAdmin()) {
            abort(403, 'Unauthorized. Access restricted to store administrators.');
        }
    }

    public function dismissAlert(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    // Segment List Search
    public function updatedSearchSegment(): void
    {
        $this->resetPage('segmentsPage');
    }

    public function updatedSearchMembers(): void
    {
        $this->resetPage('membersPage');
        $this->selectedMembers = [];
        $this->selectAllMembers = false;
    }

    public function updatedCustomerSearch(): void
    {
        $this->resetPage('addCustomersPage');
        $this->selectedCustomers = [];
        $this->selectAllCustomers = false;
    }

    public function updatedName(string $value): void
    {
        if (! $this->editingSegmentId && empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    // Segment Form
    public function openCreateSegment(): void
    {
        $this->authorizeAccess();
        $this->resetValidation();
        $this->editingSegmentId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->color = 'blue';
        $this->is_active = true;
        $this->showSegmentModal = true;
    }

    public function openEditSegment(int $id): void
    {
        $this->authorizeAccess();
        $this->resetValidation();
        $segment = CustomerSegment::findOrFail($id);

        $this->editingSegmentId = $segment->id;
        $this->name = $segment->name;
        $this->slug = $segment->slug;
        $this->description = $segment->description ?? '';
        $this->color = $segment->color ?: 'blue';
        $this->is_active = (bool) $segment->is_active;

        $this->showSegmentModal = true;
    }

    public function closeSegmentModal(): void
    {
        $this->showSegmentModal = false;
        $this->resetValidation();
    }

    public function saveSegment(): void
    {
        $this->authorizeAccess();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('customer_segments', 'slug')->ignore($this->editingSegmentId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'string', Rule::in(['blue', 'indigo', 'purple', 'emerald', 'amber', 'rose', 'cyan', 'teal'])],
            'is_active' => ['boolean'],
        ];

        $validated = $this->validate($rules);

        if ($this->editingSegmentId) {
            $segment = CustomerSegment::findOrFail($this->editingSegmentId);
            $segment->update($validated);
            $this->successMessage = "Customer segment '{$segment->name}' updated successfully.";
        } else {
            $segment = CustomerSegment::create($validated);
            $this->successMessage = "Customer segment '{$segment->name}' created successfully.";
            $this->activeSegmentId = $segment->id;
        }

        $this->showSegmentModal = false;
    }

    public function confirmDeleteSegment(int $id): void
    {
        $this->authorizeAccess();
        $this->deleteSegmentId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteSegment(): void
    {
        $this->authorizeAccess();

        if (! $this->deleteSegmentId) {
            return;
        }

        $segment = CustomerSegment::findOrFail($this->deleteSegmentId);
        $name = $segment->name;

        // Detach all customers and delete
        $segment->customers()->detach();
        $segment->delete();

        if ($this->activeSegmentId === $this->deleteSegmentId) {
            $this->activeSegmentId = null;
        }

        $this->showDeleteModal = false;
        $this->deleteSegmentId = null;
        $this->successMessage = "Customer segment '{$name}' has been deleted.";
    }

    // Member Inspection
    public function viewSegmentMembers(int $id): void
    {
        $this->activeSegmentId = $id;
        $this->searchMembers = '';
        $this->selectedMembers = [];
        $this->selectAllMembers = false;
        $this->resetPage('membersPage');
    }

    public function closeSegmentMembers(): void
    {
        $this->activeSegmentId = null;
        $this->searchMembers = '';
        $this->selectedMembers = [];
        $this->selectAllMembers = false;
    }

    // Bulk selection inside segment members
    public function updatedSelectAllMembers(bool $value): void
    {
        if (! $this->activeSegmentId) {
            return;
        }

        $pageCustomerIds = $this->getCurrentMemberPageIds();

        if ($value) {
            $this->selectedMembers = array_values(array_unique(array_merge(
                array_map('strval', $this->selectedMembers),
                $pageCustomerIds
            )));
        } else {
            $this->selectedMembers = array_values(array_diff(
                array_map('strval', $this->selectedMembers),
                $pageCustomerIds
            ));
        }

        $this->syncSelectAllMembersState();
    }

    public function updatedSelectedMembers(): void
    {
        $this->syncSelectAllMembersState();
    }

    protected function syncSelectAllMembersState(): void
    {
        $pageIds = $this->getCurrentMemberPageIds();
        $selectedStrings = array_map('strval', $this->selectedMembers);

        $this->selectAllMembers = ! empty($pageIds) && empty(array_diff($pageIds, $selectedStrings));
    }

    protected function getCurrentMemberPageIds(): array
    {
        if (! $this->activeSegmentId) {
            return [];
        }

        $segment = CustomerSegment::find($this->activeSegmentId);
        if (! $segment) {
            return [];
        }

        return $segment->customers()
            ->when($this->searchMembers, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->searchMembers.'%')
                        ->orWhere('email', 'like', '%'.$this->searchMembers.'%');
                });
            })
            ->latest('customer_segment_user.created_at')
            ->paginate(15, ['*'], 'membersPage')
            ->pluck('users.id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function removeSelectedMembers(): void
    {
        $this->authorizeAccess();

        if (! $this->activeSegmentId || empty($this->selectedMembers)) {
            $this->errorMessage = 'No customers were selected for removal.';

            return;
        }

        $segment = CustomerSegment::findOrFail($this->activeSegmentId);
        $count = count($this->selectedMembers);

        $segment->customers()->detach($this->selectedMembers);

        $this->selectedMembers = [];
        $this->selectAllMembers = false;
        $this->successMessage = "Removed {$count} customer(s) from '{$segment->name}'.";
    }

    public function removeMember(int $customerId): void
    {
        $this->authorizeAccess();

        if (! $this->activeSegmentId) {
            return;
        }

        $segment = CustomerSegment::findOrFail($this->activeSegmentId);
        $segment->customers()->detach($customerId);

        $this->selectedMembers = array_values(array_diff(
            array_map('strval', $this->selectedMembers),
            [(string) $customerId]
        ));
        $this->syncSelectAllMembersState();

        $this->successMessage = "Customer removed from '{$segment->name}'.";
    }

    // Add Customers Modal
    public function openAddCustomersModal(): void
    {
        $this->authorizeAccess();

        if (! $this->activeSegmentId) {
            return;
        }

        $this->customerSearch = '';
        $this->selectedCustomers = [];
        $this->selectAllCustomers = false;
        $this->assignmentMode = 'add';
        $this->resetPage('addCustomersPage');
        $this->showAddCustomersModal = true;
    }

    public function closeAddCustomersModal(): void
    {
        $this->showAddCustomersModal = false;
        $this->selectedCustomers = [];
        $this->selectAllCustomers = false;
    }

    public function updatedSelectAllCustomers(bool $value): void
    {
        $pageIds = $this->getAvailableCustomerPageIds();

        if ($value) {
            $this->selectedCustomers = array_values(array_unique(array_merge(
                array_map('strval', $this->selectedCustomers),
                $pageIds
            )));
        } else {
            $this->selectedCustomers = array_values(array_diff(
                array_map('strval', $this->selectedCustomers),
                $pageIds
            ));
        }

        $this->syncSelectAllCustomersState();
    }

    public function updatedSelectedCustomers(): void
    {
        $this->syncSelectAllCustomersState();
    }

    protected function syncSelectAllCustomersState(): void
    {
        $pageIds = $this->getAvailableCustomerPageIds();
        $selectedStrings = array_map('strval', $this->selectedCustomers);

        $this->selectAllCustomers = ! empty($pageIds) && empty(array_diff($pageIds, $selectedStrings));
    }

    protected function getAvailableCustomerPageIds(): array
    {
        return User::query()
            ->when($this->customerSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->customerSearch.'%')
                        ->orWhere('email', 'like', '%'.$this->customerSearch.'%');
                });
            })
            ->latest()
            ->paginate(10, ['*'], 'addCustomersPage')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function assignCustomersToSegment(): void
    {
        $this->authorizeAccess();

        if (! $this->activeSegmentId) {
            $this->errorMessage = 'No active segment is selected.';

            return;
        }

        if (empty($this->selectedCustomers)) {
            $this->errorMessage = 'Please select at least one customer to add to the segment.';

            return;
        }

        $segment = CustomerSegment::findOrFail($this->activeSegmentId);
        $customerIds = array_map('intval', $this->selectedCustomers);

        if ($this->assignmentMode === 'move') {
            // "Move" mode: remove these customers from any other segment first, then attach to this segment
            foreach ($customerIds as $cId) {
                $user = User::find($cId);
                if ($user) {
                    $user->segments()->sync([$segment->id]);
                }
            }
            $this->successMessage = 'Moved '.count($customerIds)." customer(s) exclusively into '{$segment->name}'.";
        } else {
            // "Add" mode: attach to this segment without detaching from other segments
            $segment->customers()->syncWithoutDetaching($customerIds);
            $this->successMessage = 'Added '.count($customerIds)." customer(s) to '{$segment->name}'.";
        }

        $this->showAddCustomersModal = false;
        $this->selectedCustomers = [];
        $this->selectAllCustomers = false;
    }

    public function render()
    {
        $segmentsQuery = CustomerSegment::query()
            ->withCount('customers')
            ->when($this->searchSegment, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->searchSegment.'%')
                        ->orWhere('slug', 'like', '%'.$this->searchSegment.'%')
                        ->orWhere('description', 'like', '%'.$this->searchSegment.'%');
                });
            })
            ->orderBy('name');

        $segments = (clone $segmentsQuery)->paginate(12, ['*'], 'segmentsPage');

        // Stats calculations
        $totalSegments = CustomerSegment::count();
        $totalSegmentedCustomers = DB::table('customer_segment_user')
            ->distinct('user_id')
            ->count('user_id');
        $largestSegment = CustomerSegment::withCount('customers')
            ->orderByDesc('customers_count')
            ->first();

        // Active Segment & Members
        $activeSegment = null;
        $segmentMembers = collect();

        if ($this->activeSegmentId) {
            $activeSegment = CustomerSegment::withCount('customers')->find($this->activeSegmentId);

            if ($activeSegment) {
                $segmentMembers = $activeSegment->customers()
                    ->withCount('orders')
                    ->withSum('orders', 'total')
                    ->when($this->searchMembers, function ($query) {
                        $query->where(function ($q) {
                            $q->where('users.name', 'like', '%'.$this->searchMembers.'%')
                                ->orWhere('users.email', 'like', '%'.$this->searchMembers.'%');
                        });
                    })
                    ->latest('customer_segment_user.created_at')
                    ->paginate(15, ['*'], 'membersPage');

                // Keep member selectAll in sync
                $memberPageIds = $segmentMembers->pluck('id')->map(fn ($id) => (string) $id)->all();
                $this->selectAllMembers = ! empty($memberPageIds) && empty(array_diff($memberPageIds, array_map('strval', $this->selectedMembers)));
            } else {
                $this->activeSegmentId = null;
            }
        }

        // Available Customers for "Add to Segment" modal
        $availableCustomers = collect();
        if ($this->showAddCustomersModal && $activeSegment) {
            $availableCustomers = User::query()
                ->with(['segments'])
                ->withCount('orders')
                ->withSum('orders', 'total')
                ->when($this->customerSearch, function ($query) {
                    $query->where(function ($q) {
                        $q->where('name', 'like', '%'.$this->customerSearch.'%')
                            ->orWhere('email', 'like', '%'.$this->customerSearch.'%');
                    });
                })
                ->latest()
                ->paginate(10, ['*'], 'addCustomersPage');

            $addPageIds = $availableCustomers->pluck('id')->map(fn ($id) => (string) $id)->all();
            $this->selectAllCustomers = ! empty($addPageIds) && empty(array_diff($addPageIds, array_map('strval', $this->selectedCustomers)));
        }

        return view('livewire.admin.customer-segments.index', [
            'segments' => $segments,
            'totalSegments' => $totalSegments,
            'totalSegmentedCustomers' => $totalSegmentedCustomers,
            'largestSegment' => $largestSegment,
            'activeSegment' => $activeSegment,
            'segmentMembers' => $segmentMembers,
            'availableCustomers' => $availableCustomers,
        ]);
    }
}

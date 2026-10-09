<?php

namespace App\Livewire\Admin\Customers;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $customers = User::query()
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->when(
                $this->search,
                function ($query) {
                    $query->where(function ($query) {
                        $query
                            ->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
                }
            )
            ->latest()
            ->paginate(15);

        return view('livewire.admin.customers.index', [
            'customers' => $customers,
        ])->layout('layouts.app');
    }
}
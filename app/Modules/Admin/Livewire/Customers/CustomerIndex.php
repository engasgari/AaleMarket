<?php

namespace App\Modules\Admin\Livewire\Customers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('مشتریان')]
class CustomerIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('customers.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $customers = User::query()
            ->withCount('orders')
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))
            ->when($this->search !== '', function ($q) {
                $term = $this->search;
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', '%'.$term.'%')
                        ->orWhere('mobile', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.admin.customers.customer-index', [
            'customers' => $customers,
        ]);
    }
}
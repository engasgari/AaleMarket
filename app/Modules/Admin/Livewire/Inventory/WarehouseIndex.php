<?php

namespace App\Modules\Admin\Livewire\Inventory;

use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('انبارها')]
class WarehouseIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('inventory.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $warehouses = Warehouse::query()
            ->when($this->search !== '', fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%');
            }))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.admin.inventory.warehouse-index', [
            'warehouses' => $warehouses,
        ]);
    }
}
<?php

namespace App\Modules\Admin\Livewire\Inventory;

use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('گردش موجودی')]
class MovementIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public ?int $warehouseId = null;

    #[Url(except: '')]
    public string $type = '';

    public function mount(): void
    {
        $this->authorize('inventory.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedWarehouseId(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $movements = InventoryMovement::query()
            ->with(['warehouse', 'product', 'productVariant', 'user'])
            ->when($this->warehouseId, fn ($q) => $q->where('warehouse_id', $this->warehouseId))
            ->when($this->type !== '', fn ($q) => $q->where('type', $this->type))
            ->when($this->search !== '', function ($q) {
                $q->whereHas('product', fn ($p) => $p->where('name', 'like', '%'.$this->search.'%'));
            })
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('livewire.admin.inventory.movement-index', [
            'movements' => $movements,
            'warehouses' => Warehouse::query()->orderBy('name')->get(),
        ]);
    }
}
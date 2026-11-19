<?php

namespace App\Modules\Admin\Livewire\Inventory;

use App\Modules\Inventory\Models\InventoryStock;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('موجودی')]
class StockIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public ?int $warehouseId = null;

    public ?int $activeStockId = null;

    public string $mode = '';

    public int $adjustNewQuantity = 0;

    public string $adjustReason = '';

    public int $stockInQuantity = 0;

    public ?string $stockInNotes = null;

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

    public function openAdjust(int $stockId): void
    {
        $this->authorize('inventory.update');
        $stock = InventoryStock::query()->findOrFail($stockId);
        $this->activeStockId = $stockId;
        $this->mode = 'adjust';
        $this->adjustNewQuantity = (int) $stock->quantity;
        $this->adjustReason = '';
        $this->stockInQuantity = 0;
        $this->stockInNotes = null;
    }

    public function openStockIn(int $stockId): void
    {
        $this->authorize('inventory.update');
        $this->activeStockId = $stockId;
        $this->mode = 'stock_in';
        $this->stockInQuantity = 0;
        $this->stockInNotes = null;
    }

    public function closePanel(): void
    {
        $this->activeStockId = null;
        $this->mode = '';
    }

    public function submitAdjust(InventoryService $inventory): void
    {
        $this->authorize('inventory.update');

        $this->validate([
            'adjustNewQuantity' => ['required', 'integer', 'min:0'],
            'adjustReason' => ['required', 'string', 'max:500'],
        ]);

        $stock = InventoryStock::query()->findOrFail($this->activeStockId);

        $inventory->adjust(
            (int) $stock->warehouse_id,
            (int) $stock->product_id,
            $this->adjustNewQuantity,
            $this->adjustReason,
            $stock->product_variant_id ? (int) $stock->product_variant_id : null,
            auth()->id()
        );

        session()->flash('status', 'موجودی تعدیل شد.');
        $this->closePanel();
    }

    public function submitStockIn(InventoryService $inventory): void
    {
        $this->authorize('inventory.update');

        $this->validate([
            'stockInQuantity' => ['required', 'integer', 'min:1'],
            'stockInNotes' => ['nullable', 'string', 'max:500'],
        ]);

        $stock = InventoryStock::query()->findOrFail($this->activeStockId);

        $inventory->stockIn(
            (int) $stock->warehouse_id,
            (int) $stock->product_id,
            $this->stockInQuantity,
            $stock->product_variant_id ? (int) $stock->product_variant_id : null,
            $this->stockInNotes,
            auth()->id()
        );

        session()->flash('status', 'ورود موجودی ثبت شد.');
        $this->closePanel();
    }

    public function render(): View
    {
        $stocks = InventoryStock::query()
            ->with(['warehouse', 'product', 'productVariant'])
            ->when($this->warehouseId, fn ($q) => $q->where('warehouse_id', $this->warehouseId))
            ->when($this->search !== '', function ($q) {
                $q->whereHas('product', fn ($p) => $p->where('name', 'like', '%'.$this->search.'%'));
            })
            ->orderByDesc('id')
            ->paginate(20);

        return view('livewire.admin.inventory.stock-index', [
            'stocks' => $stocks,
            'warehouses' => Warehouse::query()->orderBy('name')->get(),
        ]);
    }
}
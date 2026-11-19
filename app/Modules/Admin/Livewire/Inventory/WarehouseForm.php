<?php

namespace App\Modules\Admin\Livewire\Inventory;

use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class WarehouseForm extends Component
{
    public ?Warehouse $warehouse = null;

    public string $name = '';

    public string $code = '';

    public ?string $address = null;

    public bool $is_active = true;

    public bool $is_default = false;

    public function mount(?Warehouse $warehouse = null): void
    {
        $this->warehouse = $warehouse;

        if ($warehouse) {
            $this->authorize('inventory.update');
            $this->name = $warehouse->name;
            $this->code = $warehouse->code;
            $this->address = $warehouse->address;
            $this->is_active = (bool) $warehouse->is_active;
            $this->is_default = (bool) $warehouse->is_default;
        } else {
            $this->authorize('inventory.update');
        }
    }

    public function save(): void
    {
        $this->authorize('inventory.update');

        $warehouseId = $this->warehouse?->id;

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('warehouses', 'code')->ignore($warehouseId),
            ],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
        ]);

        DB::transaction(function () use ($warehouseId) {
            $payload = [
                'name' => $this->name,
                'code' => $this->code,
                'address' => $this->address,
                'is_active' => $this->is_active,
                'is_default' => $this->is_default,
            ];

            if ($this->warehouse) {
                $this->warehouse->update($payload);
            } else {
                $this->warehouse = Warehouse::query()->create($payload);
            }

            if ($this->is_default) {
                Warehouse::query()
                    ->whereKeyNot($this->warehouse->id)
                    ->update(['is_default' => false]);
            }
        });

        session()->flash('status', 'انبار ذخیره شد.');
        $this->redirect(route('admin.warehouses.edit', $this->warehouse), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.inventory.warehouse-form')
            ->title($this->warehouse ? 'ویرایش انبار' : 'انبار جدید');
    }
}
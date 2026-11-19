<?php

namespace App\Modules\Admin\Livewire\Catalog;

use App\Modules\Catalog\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('محصولات')]
class ProductIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';

    /** @var list<int> */
    public array $selected = [];

    public function mount(): void
    {
        $this->authorize('products.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function bulkPublish(): void
    {
        $this->authorize('products.update');

        if ($this->selected === []) {
            return;
        }

        Product::query()
            ->whereIn('id', $this->selected)
            ->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

        $this->selected = [];
        session()->flash('status', 'محصولات انتخاب‌شده منتشر شدند.');
    }

    public function bulkDraft(): void
    {
        $this->authorize('products.update');

        if ($this->selected === []) {
            return;
        }

        Product::query()
            ->whereIn('id', $this->selected)
            ->update([
                'status' => 'draft',
                'published_at' => null,
            ]);

        $this->selected = [];
        session()->flash('status', 'محصولات انتخاب‌شده به پیش‌نویس تغییر کردند.');
    }

    public function render(): View
    {
        $products = Product::query()
            ->with('brand:id,name')
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('sku', 'like', $term)
                        ->orWhere('product_code', 'like', $term);
                });
            })
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest('id')
            ->paginate(15);

        return view('livewire.admin.catalog.product-index', [
            'products' => $products,
        ]);
    }
}
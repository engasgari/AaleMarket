<?php

namespace App\Modules\Catalog\Livewire\Concerns;

use App\Modules\Cart\Services\CartService;
use App\Modules\Catalog\Services\CatalogQueryService;
use Livewire\WithPagination;

trait InteractsWithProductListing
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedBrandId(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function quickAdd(int $productId, CartService $cart): void
    {
        try {
            $cart->addItem($productId, 1);
            $this->dispatch('toast', message: 'محصول به سبد خرید اضافه شد.', type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    protected function listingFilters(): array
    {
        return [
            'search' => $this->search,
            'category_id' => $this->categoryId,
            'brand_id' => $this->brandId,
            'sort' => $this->sort,
        ];
    }

    protected function paginatedProducts(CatalogQueryService $catalog)
    {
        return $catalog->paginatePublished($this->listingFilters(), 12);
    }
}
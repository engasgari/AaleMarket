<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Cart\Services\CartService;
use App\Modules\Catalog\Services\CatalogQueryService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('آله مارکت')]
class HomePage extends Component
{
    public function quickAdd(int $productId, CartService $cart): void
    {
        try {
            $cart->addItem($productId, 1);
            $this->dispatch('toast', message: 'محصول به سبد خرید اضافه شد.', type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(CatalogQueryService $catalog)
    {
        return view('livewire.catalog.home-page', [
            'featuredCategories' => $catalog->featuredParentCategories(8),
            'featuredProducts' => $catalog->featuredProducts(3),
            'newProducts' => $catalog->newProducts(8),
            'bestSellers' => $catalog->bestSellers(8),
        ])->layout('layouts.storefront', [
            'title' => 'آله مارکت',
            'metaDescription' => 'آله مارکت — فروشگاه آنلاین تجهیزات الکترونیکی، شبکه، دوربین مداربسته و اتوماسیون صنعتی',
        ]);
    }
}
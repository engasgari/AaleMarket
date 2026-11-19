<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Livewire\Concerns\InteractsWithProductListing;
use App\Modules\Catalog\Services\CatalogQueryService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.storefront')]
class ProductIndex extends Component
{
    use InteractsWithProductListing;

    #[Url(as: 'q', history: true, except: '')]
    public string $search = '';

    #[Url(as: 'category', history: true, except: '')]
    public ?int $categoryId = null;

    #[Url(as: 'brand', history: true, except: '')]
    public ?int $brandId = null;

    #[Url(as: 'sort', history: true, except: 'newest')]
    public string $sort = 'newest';

    public function mount(): void
    {
        if ($this->search === '' && request()->filled('q')) {
            $this->search = (string) request('q', '');
        }
    }

    public function render(CatalogQueryService $catalog)
    {
        return view('livewire.catalog.product-index', [
            'products' => $this->paginatedProducts($catalog),
            'brands' => $catalog->activeBrandsForFilter(),
        ])->layout('layouts.storefront', [
            'title' => 'محصولات | آله مارکت',
            'metaDescription' => 'لیست محصولات آله مارکت — تجهیزات الکترونیکی، شبکه و اتوماسیون صنعتی',
        ]);
    }
}
<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Livewire\Concerns\InteractsWithProductListing;
use App\Modules\Catalog\Services\CatalogQueryService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.storefront')]
class SearchPage extends Component
{
    use InteractsWithProductListing;

    #[Url(as: 'q', history: true, except: '')]
    public string $search = '';

    #[Url(as: 'brand', history: true, except: '')]
    public ?int $brandId = null;

    #[Url(as: 'sort', history: true, except: 'newest')]
    public string $sort = 'newest';

    public ?int $categoryId = null;

    public function mount(): void
    {
        if ($this->search === '') {
            $this->search = (string) request('q', '');
        }
    }

    public function render(CatalogQueryService $catalog)
    {
        $title = $this->search !== ''
            ? 'جستجو: '.$this->search.' | آله مارکت'
            : 'جستجو | آله مارکت';

        return view('livewire.catalog.search-page', [
            'products' => $this->paginatedProducts($catalog),
            'brands' => $catalog->activeBrandsForFilter(),
        ])->layout('layouts.storefront', [
            'title' => $title,
            'metaDescription' => 'نتایج جستجو در آله مارکت',
        ]);
    }
}
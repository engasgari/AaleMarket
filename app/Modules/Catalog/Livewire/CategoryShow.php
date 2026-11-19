<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Livewire\Concerns\InteractsWithProductListing;
use App\Modules\Catalog\Services\CatalogQueryService;
use App\Modules\Categories\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.storefront')]
class CategoryShow extends Component
{
    use InteractsWithProductListing;

    public Category $category;

    public string $search = '';

    public ?int $categoryId = null;

    #[Url(as: 'brand', history: true, except: '')]
    public ?int $brandId = null;

    #[Url(as: 'sort', history: true, except: 'newest')]
    public string $sort = 'newest';

    public function mount(Category $category): void
    {
        if (! $category->is_active) {
            abort(404);
        }

        $this->category = $category;
        $this->categoryId = $category->id;
    }

    public function render(CatalogQueryService $catalog)
    {
        $title = ($this->category->seo_title ?: $this->category->name).' | آله مارکت';
        $description = $this->category->seo_description
            ?: ($this->category->description ?: 'محصولات دسته '.$this->category->name);

        return view('livewire.catalog.category-show', [
            'products' => $this->paginatedProducts($catalog),
            'brands' => $catalog->activeBrandsForFilter(),
        ])->layout('layouts.storefront', [
            'title' => $title,
            'metaDescription' => $description,
        ]);
    }
}
<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Product;
use App\Modules\Categories\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CatalogQueryService
{
    public function publishedQuery(): Builder
    {
        return Product::query()
            ->where('status', 'published')
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * @param  array{search?: ?string, category_id?: ?int, brand_id?: ?int, sort?: string}  $filters
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function (Builder $q) use ($like): void {
                $q->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('product_code', 'like', $like);
            });
        }

        $categoryId = $filters['category_id'] ?? null;
        if ($categoryId) {
            $query->whereHas('categories', fn (Builder $q) => $q->where('categories.id', (int) $categoryId));
        }

        $brandId = $filters['brand_id'] ?? null;
        if ($brandId) {
            $query->where('brand_id', (int) $brandId);
        }

        $this->applySort($query, (string) ($filters['sort'] ?? 'newest'));

        return $query;
    }

    /**
     * @param  array{search?: ?string, category_id?: ?int, brand_id?: ?int, sort?: string}  $filters
     */
    public function paginatePublished(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->publishedQuery()
            ->with(['brand', 'images' => fn ($q) => $q->orderBy('sort_order')->limit(1)]);

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    public function featuredProducts(int $limit = 8): Collection
    {
        $preferredSlugs = [
            'priz-houshmand',
            'ghalade-houshmand',
            'ups-houshmand',
        ];

        $products = $this->publishedQuery()
            ->where('is_featured', true)
            ->with(['brand', 'images' => fn ($q) => $q->orderBy('sort_order')->limit(1)])
            ->orderByDesc('created_at')
            ->limit(max($limit, count($preferredSlugs)))
            ->get();

        return $products
            ->sortBy(function ($product) use ($preferredSlugs) {
                $index = array_search($product->slug, $preferredSlugs, true);

                return $index === false ? 1000 + $product->id : $index;
            })
            ->take($limit)
            ->values();
    }

    public function newProducts(int $limit = 8): Collection
    {
        return $this->publishedQuery()
            ->with(['brand', 'images' => fn ($q) => $q->orderBy('sort_order')->limit(1)])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function bestSellers(int $limit = 8): Collection
    {
        return $this->publishedQuery()
            ->with(['brand', 'images' => fn ($q) => $q->orderBy('sort_order')->limit(1)])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function featuredParentCategories(int $limit = 8): Collection
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();

        // One sample product image per category (eager limit() is not per-parent).
        $categories->each(function (Category $category): void {
            $sample = $category->products()
                ->where('status', 'published')
                ->where(function (Builder $query): void {
                    $query->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
                })
                ->whereNotNull('main_image_path')
                ->orderByDesc('is_featured')
                ->orderByDesc('created_at')
                ->first();

            $category->setRelation('products', new Collection($sample ? [$sample] : []));
        });

        return $categories;
    }

    public function activeBrandsForFilter(): Collection
    {
        return Brand::query()
            ->where('is_active', true)
            ->whereHas('products', fn (Builder $q) => $q->where('status', 'published'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    public function searchSuggestions(string $term, int $limit = 6): Collection
    {
        $term = trim($term);
        if (mb_strlen($term) < 2) {
            return new Collection;
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        return $this->publishedQuery()
            ->where(function (Builder $q) use ($like): void {
                $q->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('product_code', 'like', $like);
            })
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'sku', 'main_image_path', 'selling_price', 'discount_price']);
    }

    public function productsInCategory(Category $category, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $filters['category_id'] = $category->id;

        return $this->paginatePublished($filters, $perPage);
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderByRaw('COALESCE(discount_price, selling_price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(discount_price, selling_price) DESC'),
            'featured' => $query->orderByDesc('is_featured')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };
    }
}
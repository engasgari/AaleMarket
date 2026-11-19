<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductImage;
use App\Support\Helpers\PersianSlug;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ProductService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $categoryIds
     */
    public function create(array $data, array $categoryIds = [], ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($data, $categoryIds, $image) {
            $data['slug'] = $this->ensureUniqueSlug($data['slug'] ?? PersianSlug::make($data['name']));
            $data['sku'] = $this->ensureUniqueSku($data['sku'] ?? $this->generateSku());

            if ($image) {
                $data['main_image_path'] = $this->storeImage($image);
            }

            if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            /** @var Product $product */
            $product = Product::query()->create($data);

            $this->syncCategories($product, $categoryIds);

            if (! empty($data['main_image_path'])) {
                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'path' => $data['main_image_path'],
                    'alt' => $product->name,
                    'sort_order' => 0,
                    'is_main' => true,
                ]);
            }

            return $product->fresh(['brand', 'categories']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $categoryIds
     */
    public function update(Product $product, array $data, array $categoryIds = [], ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($product, $data, $categoryIds, $image) {
            if (! empty($data['slug'])) {
                $data['slug'] = $this->ensureUniqueSlug($data['slug'], $product->id);
            }

            if ($image) {
                $this->deleteImageFile($product->main_image_path);
                $data['main_image_path'] = $this->storeImage($image);
            }

            if (($data['status'] ?? $product->status) === 'published' && ! $product->published_at) {
                $data['published_at'] = now();
            }

            $product->update($data);
            $this->syncCategories($product, $categoryIds);

            if ($image && ! empty($data['main_image_path'])) {
                ProductImage::query()
                    ->where('product_id', $product->id)
                    ->where('is_main', true)
                    ->update(['is_main' => false]);

                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'path' => $data['main_image_path'],
                    'alt' => $product->name,
                    'sort_order' => 0,
                    'is_main' => true,
                ]);
            }

            return $product->fresh(['brand', 'categories']);
        });
    }

    public function duplicate(Product $product): Product
    {
        return DB::transaction(function () use ($product) {
            $product->loadMissing(['categories']);

            $copy = $product->replicate();
            $copy->name = $product->name.' (کپی)';
            $copy->slug = $this->ensureUniqueSlug(PersianSlug::make($copy->name));
            $copy->sku = $this->ensureUniqueSku($this->generateSku());
            $copy->status = 'draft';
            $copy->published_at = null;
            $copy->is_featured = false;
            $copy->save();

            $categoryIds = $product->categories->pluck('id')->all();
            $this->syncCategories($copy, $categoryIds);

            return $copy->fresh(['brand', 'categories']);
        });
    }

    protected function syncCategories(Product $product, array $categoryIds): void
    {
        $categoryIds = array_values(array_unique(array_filter($categoryIds)));

        $sync = [];
        foreach ($categoryIds as $index => $id) {
            $sync[$id] = ['is_primary' => $index === 0];
        }

        $product->categories()->sync($sync);
    }

    protected function storeImage(UploadedFile $image): string
    {
        $filename = Str::uuid()->toString().'.'.$image->getClientOriginalExtension();
        $path = 'products/'.$filename;
        $disk = Storage::disk('public');

        if (class_exists(Image::class)) {
            $encoded = (string) Image::read($image->getRealPath())
                ->scaleDown(width: 1600)
                ->toJpeg(quality: 85);
            $disk->put($path, $encoded);
        } else {
            $disk->putFileAs('products', $image, $filename);
        }

        return $path;
    }

    protected function deleteImageFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function ensureUniqueSlug(string $slug, ?int $exceptId = null): string
    {
        $base = PersianSlug::make($slug) ?: 'product';
        $candidate = $base;
        $i = 1;

        while ($this->slugExists($candidate, $exceptId)) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    protected function slugExists(string $slug, ?int $exceptId = null): bool
    {
        return Product::query()
            ->withTrashed()
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where('slug', $slug)
            ->exists();
    }

    protected function ensureUniqueSku(string $sku): string
    {
        $base = $sku;
        $candidate = $base;
        $i = 1;

        while (Product::query()->withTrashed()->where('sku', $candidate)->exists()) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    protected function generateSku(): string
    {
        return 'SKU-'.strtoupper(Str::random(8));
    }
}
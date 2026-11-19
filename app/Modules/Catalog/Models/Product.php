<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Categories\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'brand_id',
        'name',
        'slug',
        'sku',
        'product_code',
        'short_description',
        'description',
        'purchase_price',
        'selling_price',
        'discount_price',
        'min_order_qty',
        'weight',
        'length',
        'width',
        'height',
        'status',
        'is_featured',
        'stock_status',
        'main_image_path',
        'seo_title',
        'seo_description',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'brand_id' => 'integer',
            'purchase_price' => 'integer',
            'selling_price' => 'integer',
            'discount_price' => 'integer',
            'min_order_qty' => 'integer',
            'weight' => 'decimal:3',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories')
            ->withPivot('is_primary');
    }

    public function primaryCategory(): BelongsToMany
    {
        return $this->categories()->wherePivot('is_primary', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ProductTag::class, 'product_tag_relations', 'product_id', 'tag_id');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'product_related_products',
            'product_id',
            'related_product_id'
        )->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function finalPrice(): int
    {
        return (int) ($this->discount_price ?? $this->selling_price);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function mainImageUrl(): ?string
    {
        $path = $this->main_image_path;

        if (! $path && $this->relationLoaded('images')) {
            $path = $this->images->firstWhere('is_main', true)?->path
                ?? $this->images->first()?->path;
        }

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && $this->discount_price < $this->selling_price;
    }
}
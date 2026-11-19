<?php

namespace App\Modules\Cart\Models;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends Model
{
    protected $fillable = [
        'wishlist_id',
        'product_id',
        'product_variant_id',
        'item_key',
    ];

    protected function casts(): array
    {
        return [
            'wishlist_id' => 'integer',
            'product_id' => 'integer',
            'product_variant_id' => 'integer',
        ];
    }

    public static function buildItemKey(int $wishlistId, int $productId, ?int $productVariantId): string
    {
        return $wishlistId.'-'.$productId.'-'.($productVariantId ?? 0);
    }

    protected static function booted(): void
    {
        static::saving(function (WishlistItem $item) {
            if (! $item->item_key) {
                $item->item_key = self::buildItemKey(
                    (int) $item->wishlist_id,
                    (int) $item->product_id,
                    $item->product_variant_id ? (int) $item->product_variant_id : null
                );
            }
        });
    }

    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
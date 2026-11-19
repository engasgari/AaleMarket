<?php

namespace App\Modules\Cart\Models;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'item_key',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'cart_id' => 'integer',
            'product_id' => 'integer',
            'product_variant_id' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'integer',
        ];
    }

    public static function buildItemKey(int $cartId, int $productId, ?int $productVariantId): string
    {
        return $cartId.'-'.$productId.'-'.($productVariantId ?? 0);
    }

    protected static function booted(): void
    {
        static::saving(function (CartItem $item) {
            if (! $item->item_key) {
                $item->item_key = self::buildItemKey(
                    (int) $item->cart_id,
                    (int) $item->product_id,
                    $item->product_variant_id ? (int) $item->product_variant_id : null
                );
            }
        });
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function lineTotal(): int
    {
        return (int) $this->unit_price * (int) $this->quantity;
    }
}
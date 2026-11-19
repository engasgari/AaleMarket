<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryStock extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'product_variant_id',
        'stock_key',
        'quantity',
        'reserved_quantity',
        'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'warehouse_id' => 'integer',
            'product_id' => 'integer',
            'product_variant_id' => 'integer',
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }

    public static function buildStockKey(int $warehouseId, int $productId, ?int $productVariantId): string
    {
        return $warehouseId.'-'.$productId.'-'.($productVariantId ?? 0);
    }

    protected static function booted(): void
    {
        static::saving(function (InventoryStock $stock) {
            if (! $stock->stock_key) {
                $stock->stock_key = self::buildStockKey(
                    (int) $stock->warehouse_id,
                    (int) $stock->product_id,
                    $stock->product_variant_id ? (int) $stock->product_variant_id : null
                );
            }
        });
    }

    protected function availableQuantity(): Attribute
    {
        return Attribute::get(fn (): int => max(0, (int) $this->quantity - (int) $this->reserved_quantity));
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'product_id', 'product_id')
            ->where('warehouse_id', $this->warehouse_id);
    }
}
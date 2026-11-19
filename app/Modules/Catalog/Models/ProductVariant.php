<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'purchase_price',
        'selling_price',
        'discount_price',
        'is_active',
        'sort_order',
        'attribute_data',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'purchase_price' => 'integer',
            'selling_price' => 'integer',
            'discount_price' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'attribute_data' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function finalPrice(): int
    {
        return (int) ($this->discount_price ?? $this->selling_price ?? 0);
    }
}
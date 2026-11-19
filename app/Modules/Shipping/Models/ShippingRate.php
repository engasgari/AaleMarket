<?php

namespace App\Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $fillable = [
        'shipping_method_id',
        'name',
        'min_order_amount',
        'max_order_amount',
        'min_weight',
        'max_weight',
        'price',
        'estimated_days_min',
        'estimated_days_max',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'shipping_method_id' => 'integer',
            'min_order_amount' => 'integer',
            'max_order_amount' => 'integer',
            'min_weight' => 'decimal:3',
            'max_weight' => 'decimal:3',
            'price' => 'integer',
            'estimated_days_min' => 'integer',
            'estimated_days_max' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }
}
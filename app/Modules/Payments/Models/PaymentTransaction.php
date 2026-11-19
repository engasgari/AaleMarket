<?php

namespace App\Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'type',
        'status',
        'payload',
        'response',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_id' => 'integer',
            'payload' => 'array',
            'response' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
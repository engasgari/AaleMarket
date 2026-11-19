<?php

namespace App\Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'mobile',
        'driver',
        'template',
        'status',
        'provider_message_id',
        'error_message',
        'meta',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
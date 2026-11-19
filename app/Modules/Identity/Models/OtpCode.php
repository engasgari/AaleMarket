<?php

namespace App\Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = [
        'mobile',
        'code_hash',
        'purpose',
        'attempts',
        'max_attempts',
        'expires_at',
        'consumed_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public const PURPOSE_LOGIN = 'login';

    public const PURPOSE_REGISTER = 'register';

    public const PURPOSE_RESET = 'reset';

    public const PURPOSE_VERIFY = 'verify';
}
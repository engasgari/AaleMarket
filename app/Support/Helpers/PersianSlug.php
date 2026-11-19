<?php

namespace App\Support\Helpers;

use Illuminate\Support\Str;

class PersianSlug
{
    public static function make(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $value = preg_replace('/\s+/u', '-', $value) ?? $value;
        $value = preg_replace('/[^\p{L}\p{N}\-_]+/u', '', $value) ?? $value;
        $value = preg_replace('/-+/u', '-', $value) ?? $value;

        $slug = trim($value, '-');

        if ($slug !== '') {
            return $slug;
        }

        return Str::slug($value) ?: 'item-'.time();
    }
}
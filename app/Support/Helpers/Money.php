<?php

namespace App\Support\Helpers;

final class Money
{
    public static function format(int $amountRial, bool $withLabel = true): string
    {
        $formatted = number_format($amountRial);

        if (! $withLabel) {
            return $formatted;
        }

        $label = (string) config('aale.currency_label', 'ریال');

        // Guard against corrupted .env encoding (????).
        if ($label === '' || str_contains($label, '?')) {
            $label = 'ریال';
        }

        return $formatted.' '.$label;
    }

    public static function toToman(int $amountRial): int
    {
        return intdiv($amountRial, 10);
    }
}

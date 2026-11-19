<?php

namespace App\Support\Helpers;

use Carbon\CarbonInterface;
use Hekmatinasser\Verta\Verta;

final class JalaliDate
{
    public static function format(CarbonInterface|string|null $date, string $format = 'Y/m/d H:i'): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        return Verta::instance($date)->format($format);
    }

    public static function now(string $format = 'Y/m/d H:i'): string
    {
        return Verta::now()->format($format);
    }
}

<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format a number as Rupiah currency.
     *
     * @param float|int $value
     * @return string
     */
    public static function rupiah(float|int $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}

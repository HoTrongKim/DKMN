<?php

namespace App\Support;

class PriceNormalizer
{
    public static function clamp(int $amount, ?int $min = null, ?int $max = null): int
    {
        $minValue = $min ?? (int) config('payments.display_min_vnd', 1000);
        $maxValue = $max ?? (int) config('payments.display_max_vnd', 2000);

        if ($maxValue <= 0) {
            $maxValue = $minValue ?: 0;
        }

        if ($minValue > $maxValue) {
            [$minValue, $maxValue] = [$maxValue, $minValue];
        }

        if ($amount <= 0) {
            return max(0, $minValue);
        }

        return max($minValue, min($maxValue, $amount));
    }
}

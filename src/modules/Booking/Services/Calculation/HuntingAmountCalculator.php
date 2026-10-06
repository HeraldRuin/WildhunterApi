<?php

namespace Modules\Booking\Services\Calculation;

class HuntingAmountCalculator
{
    /**
     * Индивидуальная охота: цена периода — за человека.
     * Групповая: цена периода — за одну охоту, число охот как в превью брони.
     */
    public function calculate(
        float $periodPrice,
        bool $isGroupHunt,
        int $hunters,
        int $minHunters,
        int $maxHunters,
    ): float {
        if ($hunters <= 0) {
            return 0.0;
        }

        if (!$isGroupHunt) {
            return $periodPrice * $hunters;
        }

        return $periodPrice * $this->countGroupHunts($hunters, $minHunters, $maxHunters);
    }

    private function countGroupHunts(int $hunters, int $minHunters, int $maxHunters): int
    {
        if ($maxHunters < 1 || $hunters <= $maxHunters) {
            return 1;
        }

        $min = max(1, $minHunters);
        $remaining = $hunters;
        $huntCount = 0;

        while ($remaining > 0) {
            if ($remaining < $min) {
                return 1;
            }

            $remaining -= min($remaining, $maxHunters);
            $huntCount++;
        }

        return max(1, $huntCount);
    }
}

<?php

namespace Tests\Unit\Booking;

use Modules\Booking\Services\Calculation\HuntingAmountCalculator;
use PHPUnit\Framework\TestCase;

class HuntingAmountCalculatorTest extends TestCase
{
    private HuntingAmountCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new HuntingAmountCalculator();
    }

    public function test_group_hunt_charges_period_price_once_when_hunters_fit_in_one_hunt(): void
    {
        $amount = $this->calculator->calculate(
            periodPrice: 120000,
            isGroupHunt: true,
            hunters: 5,
            minHunters: 12,
            maxHunters: 16,
        );

        $this->assertSame(120000.0, $amount);
    }

    public function test_group_hunt_charges_period_price_for_each_full_hunt(): void
    {
        $amount = $this->calculator->calculate(
            periodPrice: 30000,
            isGroupHunt: true,
            hunters: 6,
            minHunters: 3,
            maxHunters: 3,
        );

        $this->assertSame(60000.0, $amount);
    }

    public function test_individual_hunt_charges_period_price_per_hunter(): void
    {
        $amount = $this->calculator->calculate(
            periodPrice: 24000,
            isGroupHunt: false,
            hunters: 5,
            minHunters: 1,
            maxHunters: 1,
        );

        $this->assertSame(120000.0, $amount);
    }
}

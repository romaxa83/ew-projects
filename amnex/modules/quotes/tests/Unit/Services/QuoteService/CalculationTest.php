<?php

declare(strict_types=1);

namespace Wezom\Quotes\Tests\Unit\Services\QuoteService;

use Wezom\Core\Testing\TestCase;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Services\QuoteService;
use Wezom\Quotes\Tests\Builders\QuoteBuilder;
use Wezom\Quotes\Tests\Builders\TerminalDistanceBuilder;
use Wezom\Settings\Models\Setting;

class CalculationTest extends TestCase
{
    protected QuoteService $service;
    protected QuoteBuilder $quoteBuilder;
    protected TerminalDistanceBuilder $terminalDistanceBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(QuoteService::class);
        $this->quoteBuilder = $this->app->make(QuoteBuilder::class);
        $this->terminalDistanceBuilder = $this->app->make(TerminalDistanceBuilder::class);
    }

    public function test_calc_fix_rate_is_palletized()
    {
        Setting::create([
            'key' => Setting::KEY_RATE_20_40_MILES,
            'value' => '200',
            'type' => 'float',
        ]);
        Setting::create([
            'key' => Setting::KEY_PRICE_FOR_PALLET,
            'value' => '100',
            'type' => 'float',
        ]);
        Setting::create([
            'key' => Setting::KEY_PRICE_FOR_STORAGE,
            'value' => '90',
            'type' => 'float',
        ]);

        $distance = $this->terminalDistanceBuilder
            ->distance_as_mile(15)
            ->create();

        /** @var Quote $model */
        $model = $this->quoteBuilder
            ->distance($distance)
            ->is_palletized(true)
            ->number_pallets(10)
            ->days_stored(3)
            ->create();


        $res = $this->service->calculation($model);

        $this->assertEquals($res->mileageRate, 200);
        $this->assertEquals($res->cargoCost, 100 * 10);
        $this->assertEquals($res->storageCost, 90 * 3);
        $this->assertEquals($res->total, $res->mileageRate + $res->cargoCost + $res->storageCost);
    }
}

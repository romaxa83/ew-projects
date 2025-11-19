<?php

declare(strict_types=1);

namespace Wezom\Quotes\Tests\Unit\Services\QuoteService;

use Wezom\Core\Testing\TestCase;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Services\QuoteService;
use Wezom\Quotes\Tests\Builders\QuoteBuilder;
use Wezom\Quotes\Tests\Builders\TerminalDistanceBuilder;
use Wezom\Settings\Models\Setting;

class CalculationAndSetTest extends TestCase
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

        $this->assertEquals($model->mileage_cost, 0);
        $this->assertEquals($model->cargo_cost, 0);
        $this->assertEquals($model->storage_cost, 0);
        $this->assertEquals($model->total, 0);
        $this->assertNull($model->payload);

        $res = $this->service->calculationAndSet($model);

        $this->assertEquals($res->id, $model->id);
        $this->assertEquals($res->mileage_cost, 200);
        $this->assertEquals($res->cargo_cost, 100 * 10);
        $this->assertEquals($res->storage_cost, 90 * 3);
        $this->assertEquals($res->total, $res->mileage_cost + $res->cargo_cost + $res->storage_cost);
        $this->assertNotNull($res->payload);
    }

    public function test_calc_fix_rate_is_palletized_but_not_storage_value()
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

        $this->assertEquals($model->mileage_cost, 0);
        $this->assertEquals($model->cargo_cost, 0);
        $this->assertEquals($model->storage_cost, 0);
        $this->assertEquals($model->total, 0);
        $this->assertNull($model->payload);

        $res = $this->service->calculationAndSet($model);

        $this->assertEquals($res->id, $model->id);
        $this->assertEquals($res->mileage_cost, 200);
        $this->assertEquals($res->cargo_cost, 100 * 10);
        $this->assertEquals($res->storage_cost, 0);
        $this->assertEquals($res->total, $res->mileage_cost + $res->cargo_cost + $res->storage_cost);
        $this->assertNotNull($res->payload);
    }
}

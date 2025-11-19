<?php

declare(strict_types=1);

namespace Wezom\Quotes\Tests\Unit\Services\Calculation\Handlers;

use Wezom\Core\Testing\TestCase;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Services\Calculation\CalcPayload;
use Wezom\Quotes\Services\Calculation\Handlers\Cargo;
use Wezom\Quotes\Tests\Builders\QuoteBuilder;
use Wezom\Settings\Models\Setting;

class CargoTest extends TestCase
{
    protected QuoteBuilder $quoteBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->quoteBuilder = $this->app->make(QuoteBuilder::class);
    }

    public function test_success_as_pallet()
    {
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

        /** @var Quote $model  */
        $model = $this->quoteBuilder
            ->is_palletized(true)
            ->number_pallets(10)
            ->days_stored(3)
            ->create();

        $payload = new CalcPayload($model);

        $this->assertNull($payload->cargoCost);

        $res = (new Cargo())->handle($payload);

        $this->assertEquals($res->cargoCost, 100 * 10);
    }

    public function test_success_as_piece()
    {
        Setting::create([
            'key' => Setting::KEY_PRICE_FOR_PIECE,
            'value' => '10.5',
            'type' => 'float',
        ]);

        /** @var Quote $model */
        $model = $this->quoteBuilder
            ->is_palletized(false)
            ->piece_count(4)
            ->create();

        $payload = new CalcPayload($model);

        $this->assertNull($payload->cargoCost);

        $res = (new Cargo())->handle($payload);

        $this->assertEquals($res->cargoCost, 10.5 * 4);
    }
}

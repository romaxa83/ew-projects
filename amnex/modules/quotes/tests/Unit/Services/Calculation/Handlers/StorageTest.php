<?php

declare(strict_types=1);

namespace Wezom\Quotes\Tests\Unit\Services\Calculation\Handlers;

use Wezom\Core\Testing\TestCase;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Services\Calculation\CalcPayload;
use Wezom\Quotes\Services\Calculation\Handlers;
use Wezom\Quotes\Tests\Builders\QuoteBuilder;
use Wezom\Settings\Models\Setting;

class StorageTest extends TestCase
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
            'key' => Setting::KEY_PRICE_FOR_STORAGE,
            'value' => '90',
            'type' => 'float',
        ]);

        /** @var Quote $model*/
        $model = $this->quoteBuilder
            ->days_stored(10)
            ->create();

        $payload = new CalcPayload($model);

        $this->assertNull($payload->storageCost);

        $res = (new Handlers\Storage())->handle($payload);

        $this->assertEquals($res->storageCost, 90 * 10);
    }
}

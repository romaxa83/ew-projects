<?php

declare(strict_types=1);

namespace Wezom\Quotes\Tests\Unit\Services\Calculation\Handlers;

use Wezom\Core\Testing\TestCase;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Services\Calculation\CalcPayload;
use Wezom\Quotes\Services\Calculation\Handlers\Total;
use Wezom\Quotes\Tests\Builders\QuoteBuilder;

class TotalTest extends TestCase
{
    protected QuoteBuilder $quoteBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->quoteBuilder = $this->app->make(QuoteBuilder::class);
    }

    public function test_success()
    {
        /** @var Quote $model */
        $model = $this->quoteBuilder
            ->is_palletized(true)
            ->number_pallets(10)
            ->create();

        $payload = new CalcPayload($model);
        $payload->cargoCost = 100;
        $payload->storageCost = 150;
        $payload->mileageRate = 200;

        $this->assertNull($payload->total);

        $res = (new Total())->handle($payload);

        $this->assertEquals($res->total, 100 + 150 + 200);
    }
}

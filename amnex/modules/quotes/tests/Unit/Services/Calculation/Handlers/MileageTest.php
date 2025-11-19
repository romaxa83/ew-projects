<?php

declare(strict_types=1);

namespace Wezom\Quotes\Tests\Unit\Services\Calculation\Handlers;

use Wezom\Core\Testing\TestCase;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Models\TerminalDistance;
use Wezom\Quotes\Services\Calculation\CalcPayload;
use Wezom\Quotes\Services\Calculation\Handlers\Mileage;
use Wezom\Quotes\Tests\Builders\QuoteBuilder;
use Wezom\Quotes\Tests\Builders\TerminalDistanceBuilder;
use Wezom\Settings\Models\Setting;

class MileageTest extends TestCase
{
    protected QuoteBuilder $quoteBuilder;
    protected TerminalDistanceBuilder $terminalDistanceBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->quoteBuilder = $this->app->make(QuoteBuilder::class);
        $this->terminalDistanceBuilder = $this->app->make(TerminalDistanceBuilder::class);
    }

    public function test_success_0_20_miles()
    {
        Setting::create([
            'key' => Setting::KEY_RATE_0_20_MILES,
            'value' => '100',
            'type' => 'float',
        ]);

        /** @var TerminalDistance $distance */
        $distance = $this->terminalDistanceBuilder
            ->distance_as_mile(5)
            ->create();

        /** @var Quote $model */
        $model = $this->quoteBuilder
            ->distance($distance)
            ->create();


        $payload = new CalcPayload($model);

        $this->assertNull($payload->mile);

        $res = (new Mileage())->handle($payload);

        $this->assertEquals($distance->distance_as_mile * 2, $res->mile);
        $this->assertEquals($res->mileageRateKey, Setting::KEY_RATE_0_20_MILES);
        $this->assertEquals($res->mileageRate, 100);
    }

    public function test_success_20_40miles()
    {
        Setting::create([
            'key' => Setting::KEY_RATE_20_40_MILES,
            'value' => '200',
            'type' => 'float',
        ]);

        /** @var TerminalDistance $distance */
        $distance = $this->terminalDistanceBuilder
            ->distance_as_mile(15)
            ->create();

        /** @var Quote $model */
        $model = $this->quoteBuilder
            ->distance($distance)
            ->create();


        $payload = new CalcPayload($model);

        $this->assertNull($payload->mile);

        $res = (new Mileage())->handle($payload);

        $this->assertEquals($distance->distance_as_mile * 2, $res->mile);
        $this->assertEquals($res->mileageRateKey, Setting::KEY_RATE_20_40_MILES);
        $this->assertEquals($res->mileageRate, 200);
    }

    public function test_success_40_60_miles()
    {
        Setting::create([
            'key' => Setting::KEY_RATE_40_60_MILES,
            'value' => '300',
            'type' => 'float',
        ]);

        /** @var TerminalDistance $distance */
        $distance = $this->terminalDistanceBuilder
            ->distance_as_mile(25)
            ->create();

        /** @var Quote $model */
        $model = $this->quoteBuilder
            ->distance($distance)
            ->create();


        $payload = new CalcPayload($model);

        $this->assertNull($payload->mile);

        $res = (new Mileage())->handle($payload);

        $this->assertEquals($distance->distance_as_mile * 2, $res->mile);
        $this->assertEquals($res->mileageRateKey, Setting::KEY_RATE_40_60_MILES);
        $this->assertEquals($res->mileageRate, 300);
    }

    public function test_success_60_80_miles()
    {
        Setting::create([
            'key' => Setting::KEY_RATE_60_80_MILES,
            'value' => '400',
            'type' => 'float',
        ]);

        /** @var TerminalDistance $distance*/
        $distance = $this->terminalDistanceBuilder
            ->distance_as_mile(35)
            ->create();

        /** @var Quote $model */
        $model = $this->quoteBuilder
            ->distance($distance)
            ->create();


        $payload = new CalcPayload($model);

        $this->assertNull($payload->mile);

        $res = (new Mileage())->handle($payload);

        $this->assertEquals($distance->distance_as_mile * 2, $res->mile);
        $this->assertEquals($res->mileageRateKey, Setting::KEY_RATE_60_80_MILES);
        $this->assertEquals($res->mileageRate, 400);
    }

    public function test_success_80_100_miles()
    {
        Setting::create([
            'key' => Setting::KEY_RATE_80_100_MILES,
            'value' => '500',
            'type' => 'float',
        ]);

        /** @var TerminalDistance $distance */
        $distance = $this->terminalDistanceBuilder
            ->distance_as_mile(45)
            ->create();

        /** @var Quote $model */
        $model = $this->quoteBuilder
            ->distance($distance)
            ->create();


        $payload = new CalcPayload($model);

        $this->assertNull($payload->mile);

        $res = (new Mileage())->handle($payload);

        $this->assertEquals($distance->distance_as_mile * 2, $res->mile);
        $this->assertEquals($res->mileageRateKey, Setting::KEY_RATE_80_100_MILES);
        $this->assertEquals($res->mileageRate, 500);
    }

    public function test_success_more_100_miles()
    {
        Setting::create([
            'key' => Setting::KEY_RATE_80_100_MILES,
            'value' => '700',
            'type' => 'float',
        ]);
        Setting::create([
            'key' => Setting::KEY_FURTHER_MILES,
            'value' => '20',
            'type' => 'int',
        ]);
        Setting::create([
            'key' => Setting::KEY_FURTHER_RATE,
            'value' => '100',
            'type' => 'float',
        ]);

        /** @var TerminalDistance $distance */
        $distance = $this->terminalDistanceBuilder
            ->distance_as_mile(124)
            ->create();

        /** @var Quote $model */
        $model = $this->quoteBuilder
            ->distance($distance)
            ->create();


        $payload = new CalcPayload($model);

        $this->assertNull($payload->mile);

        $res = (new Mileage())->handle($payload);

        $this->assertEquals($distance->distance_as_mile * 2, $res->mile);
        $this->assertEquals($res->mileageRateKey, Setting::KEY_FURTHER_RATE);
        $this->assertEquals($res->mileageRate, 1500);
    }
}

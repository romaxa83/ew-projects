<?php

namespace Tests\Unit\Models\Divisions;

use App\Models\Calculation\LocalHourlyRates;
use App\Models\Division;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\TestCase;

class DivisionTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function get_now_season_as_summer()
    {
        $tz = 'America/Chicago';
        $date = CarbonImmutable::createFromDate(2024, 4, 30, $tz);

        CarbonImmutable::setTestNow($date);

        /** @var $model Division */
        $model = $this->divisionBuilder
            ->misc([
                'local_rates_summer_from' => '03-01',
                'local_rates_summer_to' => '09-31',
                'tz' => $tz,
            ])
            ->create();

        $this->assertEquals(LocalHourlyRates::SEASON_SUMMER, $model->getNowSeason());
    }

    /** @test */
    public function get_now_season_as_winter()
    {
        $tz = 'America/Chicago';
        $date = CarbonImmutable::createFromDate(2024, 1, 30, $tz);

        CarbonImmutable::setTestNow($date);

        /** @var $model Division */
        $model = $this->divisionBuilder
            ->misc([
                'local_rates_summer_from' => '03-01',
                'local_rates_summer_to' => '09-31',
                'tz' => $tz,
            ])
            ->create();

        $this->assertEquals(LocalHourlyRates::SEASON_WINTER, $model->getNowSeason());
    }

    /** @test */
    public function get_now_season_as_default()
    {
        $tz = 'America/Chicago';
        $date = CarbonImmutable::createFromDate(2024, 1, 30, $tz);

        CarbonImmutable::setTestNow($date);

        /** @var $model Division */
        $model = $this->divisionBuilder
            ->misc([
                'local_rates_summer_from' => null,
                'local_rates_summer_to' => null,
                'tz' => null,
            ])
            ->create();

        $this->assertEquals(LocalHourlyRates::SEASON_WINTER, $model->getNowSeason());
    }
}

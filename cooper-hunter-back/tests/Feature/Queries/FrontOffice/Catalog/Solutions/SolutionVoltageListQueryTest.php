<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Solutions;

use App\Enums\Solutions\SolutionSeriesEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\GraphQL\Queries\FrontOffice\Catalog\Solutions\SolutionVoltageListQuery;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Database\Seeders\Catalog\Solutions\SolutionDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SolutionVoltageListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        Config::set('app.env', 'local');
        $this->seed(SolutionDemoSeeder::class);
    }

    public function test_find_sophia_9000(): void
    {
        $query = GraphQLQuery::query(SolutionVoltageListQuery::NAME)
            ->args(
                [
                    'zone' => SolutionZoneEnum::SINGLE(),
                    'series_id' => SolutionSeries::whereSlug(
                        SolutionSeriesEnum::SOPHIA
                    )
                        ->get()
                        ->first()
                        ->id,
                    'btu' => 9000,
                ]
            );

        $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        SolutionVoltageListQuery::NAME
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        SolutionVoltageListQuery::NAME => [
                            115,
                            230
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . SolutionVoltageListQuery::NAME);
    }

    public function test_find_sophia_18000(): void
    {
        $query = GraphQLQuery::query(SolutionVoltageListQuery::NAME)
            ->args(
                [
                    'zone' => SolutionZoneEnum::SINGLE(),
                    'series_id' => SolutionSeries::whereSlug(
                        SolutionSeriesEnum::SOPHIA
                    )
                        ->get()
                        ->first()
                        ->id,
                    'btu' => 18000,
                ]
            );

        $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        SolutionVoltageListQuery::NAME
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        SolutionVoltageListQuery::NAME => [
                            230
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . SolutionVoltageListQuery::NAME);
    }
}

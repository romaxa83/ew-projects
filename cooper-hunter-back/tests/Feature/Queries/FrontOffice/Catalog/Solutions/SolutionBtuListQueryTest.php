<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Solutions;

use App\Enums\Solutions\SolutionSeriesEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\GraphQL\Queries\FrontOffice\Catalog\Solutions\SolutionBtuListQuery;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Database\Seeders\Catalog\Solutions\SolutionDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SolutionBtuListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Config::set('app.env', 'local');

        $this->seed(SolutionDemoSeeder::class);
    }

    public function test_find_sophia(): void
    {
        $query = GraphQLQuery::query(SolutionBtuListQuery::NAME)
            ->args(
                [
                    'zone' => SolutionZoneEnum::SINGLE(),
                    'series_id' => SolutionSeries::whereSlug(
                        SolutionSeriesEnum::SOPHIA
                    )
                        ->get()
                        ->first()
                        ->id
                ]
            );

        $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        SolutionBtuListQuery::NAME
                    ]
                ]
            );
    }

    public function test_find_hyper(): void
    {
        $query = GraphQLQuery::query(SolutionBtuListQuery::NAME)
            ->args(
                [
                    'zone' => SolutionZoneEnum::SINGLE(),
                    'series_id' => SolutionSeries::whereSlug(
                        SolutionSeriesEnum::HYPER
                    )
                        ->get()
                        ->first()
                        ->id
                ]
            );

        $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        SolutionBtuListQuery::NAME
                    ]
                ]
            );
    }
}

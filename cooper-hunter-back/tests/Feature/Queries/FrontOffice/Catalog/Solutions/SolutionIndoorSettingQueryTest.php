<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Solutions;

use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\GraphQL\Queries\FrontOffice\Catalog\Solutions\SolutionIndoorSettingQuery;
use App\Models\Catalog\Solutions\Solution;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Database\Seeders\Catalog\Solutions\SolutionDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SolutionIndoorSettingQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        Config::set('app.env', 'local');
        $this->seed(SolutionDemoSeeder::class);
    }

    public function test_get_indoor_settings(): void
    {
        $outdoor = Solution::query()
            ->where('type', SolutionTypeEnum::OUTDOOR)
            ->where('zone', SolutionZoneEnum::MULTI)
            ->where('btu', 36000)
            ->first();

        $query = GraphQLQuery::query(SolutionIndoorSettingQuery::NAME)
            ->args(
                [
                    'outdoor_id' => $outdoor->id
                ]
            )
            ->select(
                [
                    'series' => [
                        'id',
                    ],
                    'btu',
                    'types'
                ]
            );

        $this->postGraphQL($query->make())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        SolutionIndoorSettingQuery::NAME => [
                            '*' => [
                                'series' => [
                                    'id',
                                ],
                                'btu',
                                'types'
                            ]
                        ]
                    ]
                ]
            );
    }
}

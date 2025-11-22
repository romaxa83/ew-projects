<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireHeights;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireHeights\TireHeightCreateMutation;
use App\Models\Dictionaries\TireHeight;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireHeightCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_tire_height(): void
    {
        $tireHeightData = [
            'active' => true,
            'value' => 110,
        ];

        $tireHeightId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireHeightCreateMutation::NAME)
                ->args(
                    [
                        'tire_height' => $tireHeightData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'value',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        TireHeightCreateMutation::NAME => [
                            'id',
                            'active',
                            'value',
                        ]
                    ]
                ]
            )
            ->json('data.' . TireHeightCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            TireHeight::class,
            [
                'id' => $tireHeightId,
                'active' => true,
                'value' => 110,
            ]
        );
    }

    public function test_empty_value(): void
    {
        $tireHeightData = [
            'active' => true,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireHeightCreateMutation::NAME)
                ->args(
                    [
                        'tire_height' => $tireHeightData
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'Field TireHeightInputType.value of required type Float! was not provided.'
                        ]
                    ]
                ]
            );
    }
}

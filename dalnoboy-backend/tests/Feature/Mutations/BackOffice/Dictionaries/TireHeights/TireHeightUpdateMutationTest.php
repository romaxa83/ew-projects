<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireHeights;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireHeights\TireHeightUpdateMutation;
use App\Models\Dictionaries\TireHeight;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireHeightUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_tire_height(): void
    {
        $tireHeight = TireHeight::factory()->create();

        $tireHeightData = [
            'active' => true,
            'value' => 100.5,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireHeightUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireHeight->id,
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
            ->assertOk();

        $this->assertDatabaseHas(
            TireHeight::class,
            [
                'id' => $tireHeight->id,
                'active' => true,
                'value' => 100.5,
            ]
        );
    }

    public function test_empty_value(): void
    {
        $tireHeight = TireHeight::factory()->create();

        $tireHeightData = [
            'active' => true,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireHeightUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireHeight->id,
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

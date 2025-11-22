<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireDiameters;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireDiameters\TireDiameterUpdateMutation;
use App\Models\Dictionaries\TireDiameter;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireDiameterUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_tire_diameter(): void
    {
        $tireDiameter = TireDiameter::factory()->create();

        $tireDiameterData = [
            'active' => true,
            'value' => '100',
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireDiameterUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireDiameter->id,
                        'tire_diameter' => $tireDiameterData
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
            TireDiameter::class,
            [
                'id' => $tireDiameter->id,
                'active' => true,
                'value' => '100',
            ]
        );
    }

    public function test_empty_value(): void
    {
        $tireDiameter = TireDiameter::factory()->create();

        $tireDiameterData = [
            'active' => true,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireDiameterUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $tireDiameter->id,
                        'tire_diameter' => $tireDiameterData
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
                            'message' => 'Field TireDiameterInputType.value of required type String! was not provided.'
                        ]
                    ]
                ]
            );
    }

}

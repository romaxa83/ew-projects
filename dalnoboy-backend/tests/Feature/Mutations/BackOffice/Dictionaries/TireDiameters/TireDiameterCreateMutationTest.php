<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireDiameters;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireDiameters\TireDiameterCreateMutation;
use App\Models\Dictionaries\TireDiameter;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireDiameterCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_tire_diameter(): void
    {
        $tireDiameterData = [
            'active' => true,
            'value' => '110',
        ];

        $tireDiameterId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireDiameterCreateMutation::NAME)
                ->args(
                    [
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
            ->assertJsonStructure(
                [
                    'data' => [
                        TireDiameterCreateMutation::NAME => [
                            'id',
                            'active',
                            'value',
                        ]
                    ]
                ]
            )
            ->json('data.' . TireDiameterCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            TireDiameter::class,
            [
                'id' => $tireDiameterId,
                'active' => true,
                'value' => '110',
            ]
        );
    }

    public function test_empty_value(): void
    {
        $tireDiameterData = [
            'active' => true,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireDiameterCreateMutation::NAME)
                ->args(
                    [
                        'tire_diameter' => $tireDiameterData
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
                            'message' => 'Field TireDiameterInputType.value of required type String! was not provided.'
                        ]
                    ]
                ]
            );
    }
}

<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireDiameters;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireDiameters\TireDiameterDeleteMutation;
use App\Models\Dictionaries\TireDiameter;
use App\Models\Dictionaries\TireSize;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireDiameterDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_tire_diameter(): void
    {
        $tireDiameter = TireDiameter::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireDiameterDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireDiameter->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireDiameterDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            TireDiameter::class,
            [
                'id' => $tireDiameter->id,
            ]
        );
    }

    public function test_delete_tire_diameter_with_tire_sizes(): void
    {
        $tireDiameter = TireDiameter::factory()->create();
        TireSize::factory()->for($tireDiameter)->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireDiameterDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireDiameter->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.has_related_entities'),
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            TireDiameter::class,
            [
                'id' => $tireDiameter->id,
            ]
        );
    }
}

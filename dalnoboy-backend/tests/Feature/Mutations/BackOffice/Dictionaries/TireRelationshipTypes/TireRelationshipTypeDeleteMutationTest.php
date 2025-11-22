<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireRelationshipTypes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireRelationshipTypes\TireRelationshipTypeDeleteMutation;
use App\Models\Dictionaries\TireRelationshipType;
use App\Models\Tires\Tire;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireRelationshipTypeDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_tire_relationship_type(): void
    {
        $tireRelationshipType = TireRelationshipType::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireRelationshipTypeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireRelationshipType->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireRelationshipTypeDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            TireRelationshipType::class,
            [
                'id' => $tireRelationshipType->id,
            ]
        );
    }

    public function test_delete_tire_relationship_type_with_tire(): void
    {
        $tireRelationshipType = TireRelationshipType::factory()->create();
        Tire::factory()->for($tireRelationshipType, 'relationshipType')->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireRelationshipTypeDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireRelationshipType->id,
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
            TireRelationshipType::class,
            [
                'id' => $tireRelationshipType->id,
            ]
        );
    }
}

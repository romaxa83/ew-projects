<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireRelationshipTypes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireRelationshipTypes\TireRelationshipTypeToggleActiveMutation;
use App\Models\Dictionaries\TireRelationshipType;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireRelationshipTypeToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_tire_relationship_type(): void
    {
        $tireRelationshipType = TireRelationshipType::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireRelationshipTypeToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$tireRelationshipType->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireRelationshipTypeToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $tireRelationshipType->refresh();
        $this->assertFalse((bool) $tireRelationshipType->active);
    }
}

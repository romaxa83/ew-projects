<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireDiameters;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireDiameters\TireDiameterToggleActiveMutation;
use App\Models\Dictionaries\TireDiameter;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireDiameterToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_tire_diameter(): void
    {
        $tireDiameter = TireDiameter::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireDiameterToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$tireDiameter->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireDiameterToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $tireDiameter->refresh();
        $this->assertFalse((bool) $tireDiameter->active);
    }
}

<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireSpecifications;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireSpecifications\TireSpecificationToggleActiveMutation;
use App\Models\Dictionaries\TireSpecification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireSpecificationToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_tire_specification(): void
    {
        $tireSpecification = TireSpecification::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSpecificationToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$tireSpecification->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireSpecificationToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $tireSpecification->refresh();
        $this->assertFalse((bool) $tireSpecification->active);
    }
}

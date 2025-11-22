<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireTypes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireTypes\TireTypeToggleActiveMutation;
use App\Models\Dictionaries\TireType;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireTypeToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_tire_type(): void
    {
        $tireType = TireType::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireTypeToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$tireType->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireTypeToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $tireType->refresh();
        $this->assertFalse((bool) $tireType->active);
    }
}

<?php

namespace Tests\Feature\Mutations\BackOffice\Tires;

use App\GraphQL\Mutations\BackOffice\Tires\TireToggleActiveMutation;
use App\Models\Tires\Tire;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_tire(): void
    {
        $tire = Tire::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$tire->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $tire->refresh();
        $this->assertFalse((bool) $tire->active);
    }
}

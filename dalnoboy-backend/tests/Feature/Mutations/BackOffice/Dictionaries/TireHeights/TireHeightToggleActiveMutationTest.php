<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireHeights;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireHeights\TireHeightToggleActiveMutation;
use App\Models\Dictionaries\TireHeight;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireHeightToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_tire_height(): void
    {
        $tireHeight = TireHeight::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireHeightToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$tireHeight->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireHeightToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $tireHeight->refresh();
        $this->assertFalse((bool) $tireHeight->active);
    }
}

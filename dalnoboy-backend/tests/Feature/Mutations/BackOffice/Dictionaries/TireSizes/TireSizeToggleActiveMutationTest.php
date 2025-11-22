<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireSizes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireSizes\TireSizeToggleActiveMutation;
use App\Models\Dictionaries\TireSize;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireSizeToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_tire_size(): void
    {
        $tireSize = TireSize::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireSizeToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$tireSize->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireSizeToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $tireSize->refresh();
        $this->assertFalse((bool) $tireSize->active);
    }
}

<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireMakes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireMakes\TireMakeToggleActiveMutation;
use App\Models\Dictionaries\TireMake;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireMakeToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_tire_make(): void
    {
        $tireMake = TireMake::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireMakeToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$tireMake->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireMakeToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $tireMake->refresh();
        $this->assertFalse((bool) $tireMake->active);
    }
}

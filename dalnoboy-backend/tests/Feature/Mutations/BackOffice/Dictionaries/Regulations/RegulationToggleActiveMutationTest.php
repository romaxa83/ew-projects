<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\Regulations;

use App\GraphQL\Mutations\BackOffice\Dictionaries\Regulations\RegulationToggleActiveMutation;
use App\Models\Dictionaries\Regulation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegulationToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_regulation(): void
    {
        $regulation = Regulation::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RegulationToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$regulation->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        RegulationToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $regulation->refresh();
        $this->assertFalse((bool) $regulation->active);
    }
}

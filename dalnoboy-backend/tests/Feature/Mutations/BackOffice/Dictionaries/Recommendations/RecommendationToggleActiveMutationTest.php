<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\Recommendations;

use App\GraphQL\Mutations\BackOffice\Dictionaries\Recommendations\RecommendationToggleActiveMutation;
use App\Models\Dictionaries\Recommendation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RecommendationToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_recommendation(): void
    {
        $recommendation = Recommendation::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RecommendationToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$recommendation->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        RecommendationToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $recommendation->refresh();
        $this->assertFalse((bool) $recommendation->active);
    }
}

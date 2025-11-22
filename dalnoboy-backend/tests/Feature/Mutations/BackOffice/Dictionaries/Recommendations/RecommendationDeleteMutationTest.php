<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\Recommendations;

use App\GraphQL\Mutations\BackOffice\Dictionaries\Recommendations\RecommendationDeleteMutation;
use App\Models\Dictionaries\Recommendation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RecommendationDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_vehicle_type(): void
    {
        $recommendation = Recommendation::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RecommendationDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $recommendation->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        RecommendationDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Recommendation::class,
            [
                'id' => $recommendation->id,
            ]
        );
    }
}

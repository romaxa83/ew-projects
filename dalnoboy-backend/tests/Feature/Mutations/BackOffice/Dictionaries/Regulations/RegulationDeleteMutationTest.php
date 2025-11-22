<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\Regulations;

use App\GraphQL\Mutations\BackOffice\Dictionaries\Regulations\RegulationDeleteMutation;
use App\Models\Dictionaries\Regulation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegulationDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_regulation(): void
    {
        $regulation = Regulation::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(RegulationDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $regulation->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        RegulationDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Regulation::class,
            [
                'id' => $regulation->id,
            ]
        );
    }
}

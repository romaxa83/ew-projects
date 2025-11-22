<?php

namespace Tests\Feature\Mutations\BackOffice\Tires;

use App\GraphQL\Mutations\BackOffice\Tires\TireDeleteMutation;
use App\Models\Tires\Tire;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_tire(): void
    {
        $tire = Tire::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tire->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Tire::class,
            [
                'id' => $tire->id,
            ]
        );
    }
}

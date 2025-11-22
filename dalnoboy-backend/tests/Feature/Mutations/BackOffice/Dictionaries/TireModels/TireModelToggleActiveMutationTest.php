<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireModels;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireModels\TireModelToggleActiveMutation;
use App\Models\Dictionaries\TireModel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireModelToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_tire_model(): void
    {
        $tireModel = TireModel::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireModelToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$tireModel->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireModelToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $tireModel->refresh();
        $this->assertFalse((bool) $tireModel->active);
    }
}

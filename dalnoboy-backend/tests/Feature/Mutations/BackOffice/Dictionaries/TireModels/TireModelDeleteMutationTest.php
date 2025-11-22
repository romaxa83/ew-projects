<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\TireModels;

use App\GraphQL\Mutations\BackOffice\Dictionaries\TireModels\TireModelDeleteMutation;
use App\Models\Dictionaries\TireModel;
use App\Models\Dictionaries\TireSpecification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TireModelDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_tire_model(): void
    {
        $tireModel = TireModel::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireModelDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireModel->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TireModelDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            TireModel::class,
            [
                'id' => $tireModel->id,
            ]
        );
    }

    public function test_delete_tire_model_with_tire_specifications(): void
    {
        $tireModel = TireModel::factory()
            ->create();
        TireSpecification::factory()
            ->for($tireModel, 'tireModel')
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TireModelDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $tireModel->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.has_related_entities'),
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            TireModel::class,
            [
                'id' => $tireModel->id,
            ]
        );
    }
}

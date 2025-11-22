<?php

namespace Tests\Feature\Mutations\BackOffice\Managers;

use App\Enums\Utilities\MorphModelNameEnum;
use App\GraphQL\Mutations\BackOffice\Managers\ManagerDeleteMutation;
use App\Models\Clients\Client;
use App\Models\Managers\Manager;
use App\Models\Phones\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManagerDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_manager(): void
    {
        $manager = Manager::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ManagerDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $manager->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ManagerDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Manager::class,
            [
                'id' => $manager->id
            ]
        );

        $this->assertDatabaseMissing(
            Phone::class,
            [
                'owner_id' => $manager->id,
                'owner_type' => MorphModelNameEnum::manager()->key
            ]
        );
    }

    public function test_try_to_delete_manager_with_clients(): void
    {
        $manager = Manager::factory()
            ->has(
                Client::factory()
            )
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ManagerDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $manager->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.managers.has_clients')
                        ]
                    ]
                ]
            );
    }
}

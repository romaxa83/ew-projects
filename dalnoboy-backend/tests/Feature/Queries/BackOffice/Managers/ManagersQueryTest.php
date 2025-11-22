<?php

namespace Tests\Feature\Queries\BackOffice\Managers;

use App\GraphQL\Queries\BackOffice\Managers\ManagersQuery;
use App\Models\Managers\Manager;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManagersQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private iterable $managers;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();

        $this->managers = Manager::factory()
            ->count(5)
            ->create();
    }

    public function test_get_all_managers(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(ManagersQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'region' => [
                                'translate' => [
                                    'title'
                                ]
                            ]
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ManagersQuery::NAME => [
                            'data' => $this
                                ->managers
                                ->map(
                                    fn(Manager $manager) => [
                                        'id' => $manager->id,
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                ]
            );
    }

    public function test_get_managers_sort_name_desc(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(ManagersQuery::NAME)
                ->args(
                    [
                        'sort' => [
                            'full_name-desc'
                        ]
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ManagersQuery::NAME => [
                            'data' => $this
                                ->managers
                                ->sortByDesc('last_name')
                                ->map(
                                    fn(Manager $manager) => [
                                        'id' => $manager->id,
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                ]
            );
    }

    public function test_get_manager_by_phone(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(ManagersQuery::NAME)
                ->args(
                    [
                        'query' => $this->managers[2]->phone->phone
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ManagersQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->managers[2]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . ManagersQuery::NAME . '.data');
    }

    public function test_get_manager_by_full_name(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(ManagersQuery::NAME)
                ->args(
                    [
                        'query' => $this->managers[4]->last_name . ' ' . $this->managers[4]->first_name
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ManagersQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->managers[4]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . ManagersQuery::NAME . '.data');
    }


}

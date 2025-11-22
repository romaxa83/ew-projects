<?php


namespace Feature\Queries\BackOffice\Alerts;


use App\Enums\Alerts\AlertModelEnum;
use App\Enums\Alerts\AlertOrderEnum;
use App\GraphQL\Queries\Common\Alerts\BaseAlertQuery;
use App\Models\Alerts\Alert;
use App\Permissions\Alerts\AlertListPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class AlertQueryTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use AdminManagerHelperTrait;

    public function test_get_alerts(): void
    {
        $admin = $this->loginByAdminManager([AlertListPermission::KEY]);

        $email = $this->faker->email;

        Alert::factory()
            ->forAdmin($admin)
            ->order(AlertOrderEnum::CREATE, ['description' => ['email' => $email]])
            ->count(2)
            ->create();

        $alerts = $admin->alerts()
            ->orderByDesc('id')
            ->get();

        $query = GraphQLQuery::query(BaseAlertQuery::NAME)
            ->select(
                [
                    'data' => [
                        'id',
                        'type',
                        'title',
                        'description',
                        'is_read',
                        'object' => [
                            'id',
                            'name'
                        ]
                    ]
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(2, 'data.' . BaseAlertQuery::NAME . '.data')
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseAlertQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'type',
                                    'title',
                                    'description',
                                    'is_read',
                                    'object' => [
                                        'id',
                                        'name'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        BaseAlertQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$alerts[0]->id,
                                    'type' => AlertModelEnum::ORDER . '_' . AlertOrderEnum::CREATE,
                                    'title' => trans(
                                        'alerts.' . AlertModelEnum::ORDER . '.' . AlertOrderEnum::CREATE . '.title'
                                    ),
                                    'description' => trans(
                                        'alerts.' . AlertModelEnum::ORDER . '.' . AlertOrderEnum::CREATE . '.description',
                                        [
                                            'email' => $email
                                        ]
                                    ),
                                    'is_read' => false,
                                    'object' => [
                                        'id' => $alerts[0]->model_id,
                                        'name' => $alerts[0]->model_type
                                    ]
                                ],
                                [
                                    'id' => (string)$alerts[1]->id,
                                    'type' => AlertModelEnum::ORDER . '_' . AlertOrderEnum::CREATE,
                                    'title' => trans(
                                        'alerts.' . AlertModelEnum::ORDER . '.' . AlertOrderEnum::CREATE . '.title'
                                    ),
                                    'description' => trans(
                                        'alerts.' . AlertModelEnum::ORDER . '.' . AlertOrderEnum::CREATE . '.description',
                                        [
                                            'email' => $email
                                        ]
                                    ),
                                    'is_read' => false,
                                    'object' => [
                                        'id' => $alerts[1]->model_id,
                                        'name' => $alerts[1]->model_type
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }
}

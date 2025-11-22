<?php


namespace Feature\Queries\BackOffice\Alerts;


use App\Enums\Alerts\AlertOrderEnum;
use App\GraphQL\Queries\Common\Alerts\BaseAlertCounterQuery;
use App\Models\Alerts\Alert;
use App\Permissions\Alerts\AlertListPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class AlertCounterQueryTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use AdminManagerHelperTrait;

    public function test_get_alert_counter(): void
    {
        $admin = $this->loginByAdminManager([AlertListPermission::KEY]);

        Alert::factory()
            ->forAdmin($admin)
            ->order(AlertOrderEnum::CREATE, ['description' => ['email' => $this->faker->email]])
            ->count(2)
            ->create();

        $query = GraphQLQuery::query(BaseAlertCounterQuery::NAME)
            ->select(
                [
                    'not_read',
                    'total'
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseAlertCounterQuery::NAME => [
                            'not_read' => 2,
                            'total' => 2,
                        ]
                    ]
                ]
            );
    }

    public function test_get_alert_counter_without_alerts(): void
    {
        $this->loginByAdminManager([AlertListPermission::KEY]);

        $query = GraphQLQuery::query(BaseAlertCounterQuery::NAME)
            ->select(
                [
                    'not_read',
                    'total'
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseAlertCounterQuery::NAME => [
                            'not_read' => 0,
                            'total' => 0,
                        ]
                    ]
                ]
            );
    }


    public function test_get_alert_counter_with_some_read(): void
    {
        $admin = $this->loginByAdminManager([AlertListPermission::KEY]);

        Alert::factory()
            ->forAdmin($admin)
            ->order(AlertOrderEnum::CREATE, ['description' => ['email' => $this->faker->email]])
            ->count(2)
            ->create();

        Alert::factory()
            ->forAdmin($admin, true)
            ->order(AlertOrderEnum::CREATE, ['description' => ['email' => $this->faker->email]])
            ->count(5)
            ->create();

        $query = GraphQLQuery::query(BaseAlertCounterQuery::NAME)
            ->select(
                [
                    'not_read',
                    'total'
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseAlertCounterQuery::NAME => [
                            'not_read' => 2,
                            'total' => 7,
                        ]
                    ]
                ]
            );
    }
}

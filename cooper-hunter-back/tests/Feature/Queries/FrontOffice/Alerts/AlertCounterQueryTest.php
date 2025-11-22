<?php

namespace Tests\Feature\Queries\FrontOffice\Alerts;

use App\Enums\Alerts\AlertModelEnum;
use App\Enums\Alerts\AlertOrderEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Queries\Common\Alerts\BaseAlertCounterQuery;
use App\Models\Alerts\Alert;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AlertCounterQueryTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;

    public function test_get_alert_counter(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        Alert::factory()
            ->forMember($technician)
            ->order(
                AlertOrderEnum::CHANGE_STATUS,
                [
                    'description' => [
                        'status' => OrderStatusEnum::getLocalizationKey() . '.' . OrderStatusEnum::PENDING_PAID
                    ]
                ]
            )
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

        $this->postGraphQL($query)
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

    public function test_get_alert_counter_with_filter(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        Alert::factory()
            ->forMember($technician)
            ->order(
                AlertOrderEnum::CHANGE_STATUS,
                [
                    'description' => [
                        'status' => OrderStatusEnum::getLocalizationKey() . '.' . OrderStatusEnum::PENDING_PAID
                    ]
                ]
            )
            ->count(2)
            ->create();

        Alert::factory()
            ->forMember($technician)
            ->request()
            ->count(3)
            ->create();

        Alert::factory()
            ->forMember($technician)
            ->technician()
            ->count(4)
            ->create();

        $query = GraphQLQuery::query(BaseAlertCounterQuery::NAME)
            ->args(
                [
                    'object_name' => [
                        AlertModelEnum::ORDER()
                            ->toScalar(),
                        AlertModelEnum::TECHNICIAN()
                            ->toScalar(),
                    ]
                ]
            )
            ->select(
                [
                    'not_read',
                    'total'
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseAlertCounterQuery::NAME => [
                            'not_read' => 6,
                            'total' => 6,
                        ]
                    ]
                ]
            );
    }

    public function test_get_alert_counter_without_alerts(): void
    {
        $this->loginAsTechnicianWithRole();

        $query = GraphQLQuery::query(BaseAlertCounterQuery::NAME)
            ->select(
                [
                    'not_read',
                    'total'
                ]
            )
            ->make();

        $this->postGraphQL($query)
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
        $technician = $this->loginAsTechnicianWithRole();

        Alert::factory()
            ->forMember($technician)
            ->order(
                AlertOrderEnum::CHANGE_STATUS,
                [
                    'description' => [
                        'status' => OrderStatusEnum::getLocalizationKey() . '.' . OrderStatusEnum::PENDING_PAID
                    ]
                ]
            )
            ->count(2)
            ->create();

        Alert::factory()
            ->forMember($technician, true)
            ->order(
                AlertOrderEnum::CHANGE_STATUS,
                [
                    'description' => [
                        'status' => OrderStatusEnum::getLocalizationKey() . '.' . OrderStatusEnum::PENDING_PAID
                    ]
                ]
            )
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

        $this->postGraphQL($query)
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

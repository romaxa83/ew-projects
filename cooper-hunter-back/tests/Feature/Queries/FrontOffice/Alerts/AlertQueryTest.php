<?php

namespace Tests\Feature\Queries\FrontOffice\Alerts;

use App\Enums\Alerts\AlertDealerEnum;
use App\Enums\Alerts\AlertModelEnum;
use App\Enums\Alerts\AlertOrderEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Queries\Common\Alerts\BaseAlertQuery;
use App\Models\Alerts\Alert;
use App\Models\Dealers\Dealer;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AlertQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_get_alerts(): void
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

        $alerts = $technician->alerts()
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
                        ],
                        'created_at'
                    ]
                ]
            )
            ->make();

        $this->postGraphQL($query)
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
                                    ],
                                    'created_at'
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
                                    'type' => AlertModelEnum::ORDER . '_' . AlertOrderEnum::CHANGE_STATUS,
                                    'title' => trans(
                                        'alerts.' . AlertModelEnum::ORDER . '.' . AlertOrderEnum::CHANGE_STATUS . '.title'
                                    ),
                                    'description' => trans(
                                        'alerts.' . AlertModelEnum::ORDER . '.' . AlertOrderEnum::CHANGE_STATUS . '.description',
                                        [
                                            'status' => trans(
                                                OrderStatusEnum::getLocalizationKey(
                                                ) . '.' . OrderStatusEnum::PENDING_PAID
                                            )
                                        ]
                                    ),
                                    'is_read' => false,
                                    'object' => [
                                        'id' => $alerts[0]->model_id,
                                        'name' => $alerts[0]->model_type
                                    ],
                                    'created_at' => $alerts[0]->created_at->getTimestamp()
                                ],
                                [
                                    'id' => (string)$alerts[1]->id,
                                    'type' => AlertModelEnum::ORDER . '_' . AlertOrderEnum::CHANGE_STATUS,
                                    'title' => trans(
                                        'alerts.' . AlertModelEnum::ORDER . '.' . AlertOrderEnum::CHANGE_STATUS . '.title'
                                    ),
                                    'description' => trans(
                                        'alerts.' . AlertModelEnum::ORDER . '.' . AlertOrderEnum::CHANGE_STATUS . '.description',
                                        [
                                            'status' => trans(
                                                OrderStatusEnum::getLocalizationKey(
                                                ) . '.' . OrderStatusEnum::PENDING_PAID
                                            )
                                        ]
                                    ),
                                    'is_read' => false,
                                    'object' => [
                                        'id' => $alerts[1]->model_id,
                                        'name' => $alerts[1]->model_type
                                    ],
                                    'created_at' => $alerts[0]->created_at->getTimestamp()
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    /** @test */
    public function get_alerts_for_dealer(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        Alert::factory()
            ->forMember($dealer)
            ->count(2)
            ->create([
                'model_type' => Dealer::MORPH_NAME,
                'model_id' => $dealer->id,
                'type' => AlertModelEnum::DEALER . '_' . AlertDealerEnum::EMAIL_VERIFICATION_READY
            ]);

        $alerts = $dealer->alerts()
            ->orderByDesc('id')
            ->get();

        $query = GraphQLQuery::query(BaseAlertQuery::NAME)
            ->select([
                'data' => [
                    'id',
                    'type',
                    'title',
                    'description',
                    'is_read',
                    'object' => [
                        'id',
                        'name'
                    ],
                    'created_at'
                ]
            ])
            ->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonCount(2, 'data.' . BaseAlertQuery::NAME . '.data')
            ->assertJson([
                'data' => [
                    BaseAlertQuery::NAME => [
                        'data' => [
                            [
                                'id' => (string)$alerts[0]->id,
                                'type' => AlertModelEnum::DEALER . '_' . AlertDealerEnum::EMAIL_VERIFICATION_READY,
                                'title' => $alerts[0]->title,
                                'description' => $alerts[0]->description,
                                'is_read' => false,
                                'object' => [
                                    'id' => $alerts[0]->model_id,
                                    'name' => $alerts[0]->model_type
                                ],
                                'created_at' => $alerts[0]->created_at->getTimestamp()
                            ],
                            [
                                'id' => (string)$alerts[1]->id,
                                'type' => AlertModelEnum::DEALER . '_' . AlertDealerEnum::EMAIL_VERIFICATION_READY,
                                'title' => $alerts[1]->title,
                                'description' => $alerts[1]->description,
                                'is_read' => false,
                                'object' => [
                                    'id' => $alerts[1]->model_id,
                                    'name' => $alerts[1]->model_type
                                ],
                                'created_at' => $alerts[1]->created_at->getTimestamp()
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_get_one_alert(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $alerts = Alert::factory()
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

        $query = GraphQLQuery::query(BaseAlertQuery::NAME)
            ->args(
                [
                    'id' => $alerts[1]->id
                ]
            )
            ->select(
                [
                    'data' => [
                        'id'
                    ]
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.' . BaseAlertQuery::NAME . '.data')
            ->assertJson(
                [
                    'data' => [
                        BaseAlertQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$alerts[1]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_filter_by_object_alert(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $orderAlert = Alert::factory()
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

        $technicianAlert = Alert::factory()
            ->forMember($technician)
            ->technician()
            ->count(4)
            ->create();

        $query = GraphQLQuery::query(BaseAlertQuery::NAME)
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
                    'data' => [
                        'id'
                    ]
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonCount(6, 'data.' . BaseAlertQuery::NAME . '.data')
            ->assertJson(
                [
                    'data' => [
                        BaseAlertQuery::NAME => [
                            'data' => [
                                [
                                    'id' => (string)$technicianAlert[3]->id,
                                ],
                                [
                                    'id' => (string)$technicianAlert[2]->id,
                                ],
                                [
                                    'id' => (string)$technicianAlert[1]->id,
                                ],
                                [
                                    'id' => (string)$technicianAlert[0]->id,
                                ],
                                [
                                    'id' => (string)$orderAlert[1]->id,
                                ],
                                [
                                    'id' => (string)$orderAlert[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }
}

<?php


namespace Feature\Queries\BackOffice\SupportRequests;

use App\GraphQL\Queries\BackOffice\SupportRequests\SupportRequestsQuery;
use App\Models\Admins\Admin;
use App\Permissions\SupportRequests\SupportRequestListPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\SupportRequestCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SupportRequestsQueryTest extends TestCase
{

    use DatabaseTransactions;
    use SupportRequestCreateTrait;
    use AdminManagerHelperTrait;

    private GraphQLQuery $query;

    private Admin $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = GraphQLQuery::query(SupportRequestsQuery::NAME)
            ->select(
                [
                    'data' => [
                        'id',
                        'is_read'
                    ]
                ]
            );
        $this->admin = $this->loginByAdminManager([SupportRequestListPermission::KEY]);
    }

    public function test_get_list(): void
    {
        $requests = $this->countSupportRequests(3)
            ->createSupportRequest();
        $this->postGraphQLBackOffice($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $requests[0]->id,
                                    'is_read' => false,
                                ],
                                [
                                    'id' => $requests[1]->id,
                                    'is_read' => false,
                                ],
                                [
                                    'id' => $requests[2]->id,
                                    'is_read' => false,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . SupportRequestsQuery::NAME . '.data');
    }

    public function test_get_list_by_technician_email(): void
    {
        $requests = $this->countSupportRequests(2)
            ->createSupportRequest();
        $this->countSupportRequests(3)
            ->createSupportRequest();
        $this->postGraphQLBackOffice(
            $this->query->args(['technician_email' => $requests[0]->technician->email])
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $requests[0]->id,
                                ],
                                [
                                    'id' => $requests[1]->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . SupportRequestsQuery::NAME . '.data');
    }

    public function test_get_list_by_technician_name(): void
    {
        $this->countSupportRequests(2)
            ->createSupportRequest();
        $requests = $this->countSupportRequests(3)
            ->createSupportRequest();
        $this->postGraphQLBackOffice(
            $this->query->args(['technician_name' => $requests[0]->technician->last_name])
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $requests[0]->id,
                                ],
                                [
                                    'id' => $requests[1]->id,
                                ],
                                [
                                    'id' => $requests[2]->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . SupportRequestsQuery::NAME . '.data');
    }

    public function test_get_list_sorting_1(): void
    {
        $requestClosed = $this->createSupportRequest(attributes: ['is_closed' => true]);

        $requestWithAdminAnswer = $this->createSupportRequest();

        $requestWithAdminAnswer->messages()
            ->create(
                [
                    'sender_id' => $this->admin->id,
                    'sender_type' => Admin::MORPH_NAME,
                    'message' => $this->faker->text
                ]
            );

        $requestWithoutAnswer1 = $this->createSupportRequest();
        $requestWithoutAnswer2 = $this->createSupportRequest();


        $this->postGraphQLBackOffice($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $requestWithoutAnswer1->id,
                                ],
                                [
                                    'id' => $requestWithoutAnswer2->id,
                                ],
                                [
                                    'id' => $requestWithAdminAnswer->id,
                                ],
                                [
                                    'id' => $requestClosed->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(4, 'data.' . SupportRequestsQuery::NAME . '.data');
    }

    public function test_get_list_sorting_2(): void
    {
        $requestWithAdminAnswer = $this->createSupportRequest();
        $requestWithAdminAnswer->messages()
            ->create(
                [
                    'sender_id' => $this->admin->id,
                    'sender_type' => Admin::MORPH_NAME,
                    'message' => $this->faker->text
                ]
            );

        $requestWithoutAnswer1 = $this->createSupportRequest();

        $requestClosed = $this->createSupportRequest(attributes: ['is_closed' => true]);

        $requestWithoutAnswer2 = $this->createSupportRequest();

        $this->postGraphQLBackOffice($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $requestWithoutAnswer1->id,
                                ],
                                [
                                    'id' => $requestWithoutAnswer2->id,
                                ],
                                [
                                    'id' => $requestWithAdminAnswer->id,
                                ],
                                [
                                    'id' => $requestClosed->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(4, 'data.' . SupportRequestsQuery::NAME . '.data');
    }

}

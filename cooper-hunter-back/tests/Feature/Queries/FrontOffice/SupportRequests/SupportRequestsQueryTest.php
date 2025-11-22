<?php


namespace Tests\Feature\Queries\FrontOffice\SupportRequests;

use App\GraphQL\Queries\FrontOffice\SupportRequests\SupportRequestsQuery;
use Carbon\Carbon;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\SupportRequestCreateTrait;

class SupportRequestsQueryTest extends TestCase
{

    use DatabaseTransactions;
    use SupportRequestCreateTrait;

    private GraphQLQuery $query;

    private Collection $requests;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = GraphQLQuery::query(SupportRequestsQuery::NAME)
            ->select(
                [
                    'data' => [
                        'id'
                    ]
                ]
            );

        $technician = $this->loginAsTechnicianWithRole();

        $this->requests = $this->countSupportRequests(3)
            ->createSupportRequest($technician);

        $this->requests[0]->created_at = Carbon::now()
            ->subDays(2)
            ->toDateTimeString();
        $this->requests[0]->save();

        $this->requests[1]->created_at = Carbon::now()
            ->subDays(4)
            ->toDateTimeString();
        $this->requests[1]->save();

        $this->requests[2]->is_closed = true;
        $this->requests[2]->save();
    }

    public function test_get_list(): void
    {
        $this->postGraphQL($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->requests[2]->id,
                                ],
                                [
                                    'id' => $this->requests[1]->id,
                                ],
                                [
                                    'id' => $this->requests[0]->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . SupportRequestsQuery::NAME . '.data');
    }

    public function test_get_list_by_id(): void
    {
        $this->postGraphQL(
            $this->query->args(['id' => $this->requests[1]->id])
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->requests[1]->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . SupportRequestsQuery::NAME . '.data');
    }

    public function test_get_list_by_date(): void
    {
        $this->postGraphQL(
            $this->query->args(
                [
                    'date_from' => Carbon::now()
                        ->subDays(3)
                        ->toDateString(),
                    'date_to' => Carbon::now()
                        ->subDays(1)
                        ->toDateString()
                ]
            )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->requests[0]->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . SupportRequestsQuery::NAME . '.data');
    }

    public function test_get_list_by_closed(): void
    {
        $this->postGraphQL(
            $this->query->args(
                [
                    'closed' => true
                ]
            )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->requests[2]->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . SupportRequestsQuery::NAME . '.data');
    }

    public function test_get_list_by_subject(): void
    {
        $this->postGraphQL(
            $this->query->args(
                [
                    'subject_id' => $this->requests[2]->subject_id
                ]
            )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->requests[2]->id,
                                ],
                                [
                                    'id' => $this->requests[1]->id,
                                ],
                                [
                                    'id' => $this->requests[0]->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . SupportRequestsQuery::NAME . '.data');
    }

}

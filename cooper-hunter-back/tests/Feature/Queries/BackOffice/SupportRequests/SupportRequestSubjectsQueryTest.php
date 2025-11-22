<?php


namespace Tests\Feature\Queries\BackOffice\SupportRequests;

use App\GraphQL\Queries\BackOffice\SupportRequests\SupportRequestSubjectsQuery;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Permissions\SupportRequests\Subjects\SupportRequestSubjectListPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SupportRequestSubjectsQueryTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    private GraphQLQuery $query;

    private SupportRequestSubject $active;
    private SupportRequestSubject $nonactive;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = GraphQLQuery::query(SupportRequestSubjectsQuery::NAME)
            ->select(
                [
                    'id'
                ]
            );

        $this->loginByAdminManager([SupportRequestSubjectListPermission::KEY]);

        $this->active = SupportRequestSubject::factory()
            ->create();
        $this->nonactive = SupportRequestSubject::factory()
            ->create(['active' => false]);
    }

    public function test_get_subject_list(): void
    {
        $this->postGraphQLBackOffice($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestSubjectsQuery::NAME => [
                            [
                                'id' => $this->nonactive->id,
                            ],
                            [
                                'id' => $this->active->id,
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . SupportRequestSubjectsQuery::NAME);
    }

    public function test_get_active_subject_list(): void
    {
        $this->postGraphQLBackOffice(
            $this->query
                ->args(
                    [
                        'published' => true
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestSubjectsQuery::NAME => [
                            [
                                'id' => $this->active->id,
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . SupportRequestSubjectsQuery::NAME);
    }

    public function test_get_nonactive_subject_list(): void
    {
        $this->postGraphQLBackOffice(
            $this->query
                ->args(
                    [
                        'published' => false
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestSubjectsQuery::NAME => [
                            [
                                'id' => $this->nonactive->id,
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . SupportRequestSubjectsQuery::NAME);
    }

    public function test_get_subject_by_id(): void
    {
        $this->postGraphQLBackOffice(
            $this->query
                ->args(
                    [
                        'id' => $this->active->id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestSubjectsQuery::NAME => [
                            [
                                'id' => $this->active->id,
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . SupportRequestSubjectsQuery::NAME);
    }

}

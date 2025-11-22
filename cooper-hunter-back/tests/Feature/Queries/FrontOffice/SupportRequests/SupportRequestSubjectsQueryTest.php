<?php


namespace Feature\Queries\FrontOffice\SupportRequests;

use App\GraphQL\Queries\FrontOffice\SupportRequests\SupportRequestSubjectsQuery;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SupportRequestSubjectsQueryTest extends TestCase
{

    use DatabaseTransactions;

    private GraphQLQuery $query;

    private SupportRequestSubject $active;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = GraphQLQuery::query(SupportRequestSubjectsQuery::NAME)
            ->select(
                [
                    'id'
                ]
            );

        $this->loginAsTechnicianWithRole();

        $this->active = SupportRequestSubject::factory()
            ->create();
        SupportRequestSubject::factory()
            ->create(['active' => false]);
    }

    public function test_get_subject_list(): void
    {
        $this->postGraphQL($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestSubjectsQuery::NAME => [
                            [
                                'id' => $this->active->id,
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . SupportRequestSubjectsQuery::NAME);
    }

    public function test_get_subject_by_id(): void
    {
        $this->postGraphQL(
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

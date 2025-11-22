<?php


namespace Feature\Mutations\BackOffice\SupportRequests\Subjects;

use App\GraphQL\Mutations\BackOffice\SupportRequests\Subjects\SupportRequestSubjectCreateMutation;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Permissions\SupportRequests\Subjects\SupportRequestSubjectCreatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SupportRequestSubjectCreateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    private GraphQLQuery $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = GraphQLQuery::mutation(SupportRequestSubjectCreateMutation::NAME)
            ->select(
                [
                    'id',
                    'translation' => [
                        'title'
                    ],
                    'translations' => [
                        'title'
                    ]
                ]
            );

        $this->loginByAdminManager([SupportRequestSubjectCreatePermission::KEY]);
    }

    public function test_create_subject(): void
    {
        $id = $this->postGraphQLBackOffice(
            $this->query->args(
                [
                    'translations' => [
                        [
                            'title' => 'en title',
                            'language' => 'en',
                        ],
                        [
                            'title' => 'es title',
                            'language' => 'es',
                        ]
                    ],
                ]
            )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        SupportRequestSubjectCreateMutation::NAME => [
                            'id',
                            'translation' => [
                                'title'
                            ],
                            'translations' => [
                                '*' => [
                                    'title'
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->json('data.' . SupportRequestSubjectCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            SupportRequestSubject::class,
            [
                'id' => $id
            ]
        );
    }

}

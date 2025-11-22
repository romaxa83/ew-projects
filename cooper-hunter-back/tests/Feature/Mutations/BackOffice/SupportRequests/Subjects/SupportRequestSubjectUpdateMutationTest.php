<?php


namespace Feature\Mutations\BackOffice\SupportRequests\Subjects;

use App\GraphQL\Mutations\BackOffice\SupportRequests\Subjects\SupportRequestSubjectUpdateMutation;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Models\Support\RequestSubjects\SupportRequestSubjectTranslation;
use App\Permissions\SupportRequests\Subjects\SupportRequestSubjectUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SupportRequestSubjectUpdateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    private GraphQLQuery $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = GraphQLQuery::mutation(SupportRequestSubjectUpdateMutation::NAME)
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

        $this->loginByAdminManager([SupportRequestSubjectUpdatePermission::KEY]);
    }

    public function test_update_subject(): void
    {
        $subject = SupportRequestSubject::factory()
            ->create();

        $this->postGraphQLBackOffice(
            $this->query->args(
                [
                    'id' => $subject->id,
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
                        SupportRequestSubjectUpdateMutation::NAME => [
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
            );

        $this->assertDatabaseHas(
            SupportRequestSubjectTranslation::class,
            [
                'row_id' => $subject->id,
                'language' => 'en',
                'title' => 'en title'
            ]
        );

        $this->assertDatabaseHas(
            SupportRequestSubjectTranslation::class,
            [
                'row_id' => $subject->id,
                'language' => 'es',
                'title' => 'es title'
            ]
        );
    }

}

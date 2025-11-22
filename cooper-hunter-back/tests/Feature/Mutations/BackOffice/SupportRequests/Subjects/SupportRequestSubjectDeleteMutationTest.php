<?php


namespace Feature\Mutations\BackOffice\SupportRequests\Subjects;

use App\GraphQL\Mutations\BackOffice\SupportRequests\Subjects\SupportRequestSubjectDeleteMutation;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Models\Support\RequestSubjects\SupportRequestSubjectTranslation;
use App\Permissions\SupportRequests\Subjects\SupportRequestSubjectDeletePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\SupportRequestCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SupportRequestSubjectDeleteMutationTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use SupportRequestCreateTrait;

    private GraphQLQuery $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = GraphQLQuery::mutation(SupportRequestSubjectDeleteMutation::NAME);

        $this->loginByAdminManager([SupportRequestSubjectDeletePermission::KEY]);
    }

    public function test_delete_subject(): void
    {
        $subject = SupportRequestSubject::factory()
            ->create();

        $this->postGraphQLBackOffice(
            $this->query->args(
                [
                    'id' => $subject->id
                ]
            )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestSubjectDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            SupportRequestSubject::class,
            [
                'id' => $subject->id
            ]
        );

        $this->assertDatabaseMissing(
            SupportRequestSubjectTranslation::class,
            [
                'row_id' => $subject->id
            ]
        );
    }

    public function test_try_to_delete_subject_with_requests(): void
    {
        $request = $this->createSupportRequest();

        $this->postGraphQLBackOffice(
            $this->query->args(
                [
                    'id' => $request->subject_id
                ]
            )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.support_request.subject_used_in_requests')
                        ]
                    ]
                ]
            );
    }

}

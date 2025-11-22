<?php


namespace Feature\Mutations\BackOffice\SupportRequests\Subjects;

use App\GraphQL\Mutations\BackOffice\SupportRequests\Subjects\SupportRequestSubjectToggleActiveMutation;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Permissions\SupportRequests\Subjects\SupportRequestSubjectUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\SupportRequestCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SupportRequestSubjectToggleActiveMutationTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use SupportRequestCreateTrait;

    private GraphQLQuery $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = GraphQLQuery::mutation(SupportRequestSubjectToggleActiveMutation::NAME)
            ->select(
                [
                    'active'
                ]
            );

        $this->loginByAdminManager([SupportRequestSubjectUpdatePermission::KEY]);
    }

    public function test_turn_off_subject(): void
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
                        SupportRequestSubjectToggleActiveMutation::NAME => [
                            'active' => false
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_turn_off_subject_with_requests(): void
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

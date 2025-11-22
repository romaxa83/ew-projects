<?php


namespace Feature\Mutations\BackOffice\SupportRequests;


use App\GraphQL\Mutations\BackOffice\SupportRequests\SupportRequestCloseMutation;
use App\GraphQL\Types\SupportRequests\SupportRequestType;
use App\Models\Support\SupportRequest;
use App\Permissions\SupportRequests\SupportRequestClosePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\SupportRequestCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SupportRequestCloseMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use SupportRequestCreateTrait;
    use AdminManagerHelperTrait;

    public function test_answer_support_request(): void
    {
        $this->loginByAdminManager([SupportRequestClosePermission::KEY]);

        $supportRequest = $this->createSupportRequest();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SupportRequestCloseMutation::NAME)
                ->args(
                    [
                        'id' => $supportRequest->id,
                    ]
                )
                ->select(
                    [
                        '__typename',
                        'id',
                        'is_closed'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestCloseMutation::NAME => [
                            '__typename' => SupportRequestType::NAME,
                            'id' => $supportRequest->id,
                            'is_closed' => true,
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            SupportRequest::class,
            [
                'id' => $supportRequest->id,
                'is_closed' => true,
            ]
        );
    }
}

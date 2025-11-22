<?php


namespace Feature\Mutations\BackOffice\SupportRequests;


use App\GraphQL\Mutations\BackOffice\SupportRequests\SupportRequestSetIsReadMutation;
use App\Models\Support\SupportRequestMessage;
use App\Models\Technicians\Technician;
use App\Permissions\SupportRequests\SupportRequestAnswerPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\SupportRequestCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SupportRequestSetIsReadMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use SupportRequestCreateTrait;
    use AdminManagerHelperTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([SupportRequestAnswerPermission::KEY]);
    }

    public function test_read_support_request(): void
    {
        $supportRequest = $this->createSupportRequest();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SupportRequestSetIsReadMutation::NAME)
                ->args(
                    [
                        'id' => $supportRequest->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestSetIsReadMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            SupportRequestMessage::class,
            [
                'support_request_id' => $supportRequest->id,
                'sender_type' => Technician::MORPH_NAME,
                'is_read' => false,
            ]
        );
    }
}

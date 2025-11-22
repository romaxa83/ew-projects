<?php


namespace Feature\Mutations\BackOffice\SupportRequests;


use App\GraphQL\Mutations\BackOffice\SupportRequests\SupportRequestAnswerMutation;
use App\GraphQL\Types\SupportRequests\SupportRequestMessageType;
use App\GraphQL\Types\Users\UserMorphType;
use App\Models\Support\SupportRequestMessage;
use App\Permissions\SupportRequests\SupportRequestAnswerPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\SupportRequestCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SupportRequestAnswerMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use SupportRequestCreateTrait;
    use AdminManagerHelperTrait;

    public function test_answer_support_request(): void
    {
        $admin = $this->loginByAdminManager([SupportRequestAnswerPermission::KEY]);

        $supportRequest = $this->createSupportRequest();

        $message = $this->faker->text;

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SupportRequestAnswerMutation::NAME)
                ->args(
                    [
                        'id' => $supportRequest->id,
                        'message' => [
                            'text' => $message,
                        ]
                    ]
                )
                ->select(
                    [
                        '__typename',
                        'id',
                        'text',
                        'sender' => [
                            '__typename',
                            'id',
                            'name',
                            'email',
                            'type',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestAnswerMutation::NAME => [
                            '__typename' => SupportRequestMessageType::NAME,
                            'text' => $message,
                            'sender' => [
                                '__typename' => UserMorphType::NAME,
                                'id' => (string)$admin->id,
                                'name' => $admin->getName(),
                                'email' => $admin->getEmail(),
                                'type' => $admin->getMorphType(),
                            ],
                        ]
                    ]
                ]
            );

        $this->assertDatabaseCount(
            SupportRequestMessage::class,
            2
        );
    }
}

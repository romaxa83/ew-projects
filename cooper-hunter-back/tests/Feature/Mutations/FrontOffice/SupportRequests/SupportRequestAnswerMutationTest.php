<?php


namespace Feature\Mutations\FrontOffice\SupportRequests;


use App\GraphQL\Mutations\FrontOffice\SupportRequests\SupportRequestAnswerMutation;
use App\GraphQL\Types\SupportRequests\SupportRequestMessageType;
use App\GraphQL\Types\Users\UserMorphType;
use App\Models\Support\SupportRequestMessage;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\SupportRequestCreateTrait;

class SupportRequestAnswerMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use SupportRequestCreateTrait;

    public function test_answer_support_request(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $supportRequest = $this->createSupportRequest($technician);

        $message = $this->faker->text;

        $this->postGraphQL(
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
                                'id' => (string)$technician->id,
                                'name' => $technician->getName(),
                                'email' => $technician->getEmail(),
                                'type' => $technician->getMorphType(),
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

    public function test_try_to_answer_on_closed_support_request(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $supportRequest = $this->createSupportRequest($technician, ['is_closed' => true]);

        $this->postGraphQL(
            GraphQLQuery::mutation(SupportRequestAnswerMutation::NAME)
                ->args(
                    [
                        'id' => $supportRequest->id,
                        'message' => [
                            'text' => $this->faker->text,
                        ]
                    ]
                )
                ->select(
                    [
                        'id'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'validation'
                        ]
                    ]
                ]
            );
    }
}

<?php


namespace Feature\Mutations\FrontOffice\SupportRequests;


use App\GraphQL\Mutations\FrontOffice\SupportRequests\SupportRequestCreateMutation;
use App\GraphQL\Types\SupportRequests\Subjects\SupportRequestSubjectTranslateType;
use App\GraphQL\Types\SupportRequests\Subjects\SupportRequestSubjectType;
use App\GraphQL\Types\SupportRequests\SupportRequestMessageType;
use App\GraphQL\Types\SupportRequests\SupportRequestType;
use App\GraphQL\Types\Technicians\TechnicianType;
use App\GraphQL\Types\Users\UserMorphType;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SupportRequestCreateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;

    public function test_create_support_request(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $subject = SupportRequestSubject::factory()
            ->create();

        $message = $this->faker->text;

        $this->postGraphQL(
            GraphQLQuery::mutation(SupportRequestCreateMutation::NAME)
                ->args(
                    [
                        'support_request' => [
                            'subject_id' => $subject->id,
                            'message' => [
                                'text' => $message
                            ]
                        ]
                    ]
                )
                ->select(
                    [
                        '__typename',
                        'id',
                        'subject' => [
                            '__typename',
                            'id',
                            'translation' => [
                                '__typename',
                                'id',
                                'title'
                            ]
                        ],
                        'technician' => [
                            '__typename',
                            'id'
                        ],
                        'messages' => [
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
                        ],
                        'is_closed'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestCreateMutation::NAME => [
                            '__typename' => SupportRequestType::NAME,
                            'subject' => [
                                '__typename' => SupportRequestSubjectType::NAME,
                                'id' => (string)$subject->id,
                                'translation' => [
                                    '__typename' => SupportRequestSubjectTranslateType::NAME,
                                    'id' => (string)$subject->translation->id,
                                    'title' => $subject->translation->title,
                                ],
                            ],
                            'technician' => [
                                '__typename' => TechnicianType::NAME,
                                'id' => (string)$technician->id,
                            ],
                            'messages' => [
                                [
                                    '__typename' => SupportRequestMessageType::NAME,
                                    'text' => $message,
                                    'sender' => [
                                        '__typename' => UserMorphType::NAME,
                                        'id' => (string)$technician->id,
                                        'name' => $technician->getName(),
                                        'email' => $technician->getEmail(),
                                        'type' => $technician->getMorphType(),
                                    ],
                                ],
                            ],
                            'is_closed' => false,
                        ]
                    ]
                ]
            );
    }
}

<?php


namespace Feature\Mutations\FrontOffice\SupportRequests;


use App\GraphQL\Mutations\FrontOffice\SupportRequests\SupportRequestSetIsReadMutation;
use App\Models\Admins\Admin;
use App\Models\Support\SupportRequestMessage;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\SupportRequestCreateTrait;

class SupportRequestSetIsReadMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use SupportRequestCreateTrait;

    public function test_read_support_request(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $supportRequest = $this->createSupportRequest($technician);

        $supportRequest->messages()
            ->create(
                [
                    'message' => $this->faker->text,
                    'sender_type' => Admin::MORPH_NAME,
                    'sender_id' => Admin::factory()
                        ->create()->id,
                ]
            );

        $supportRequest->messages()
            ->create(
                [
                    'message' => $this->faker->text,
                    'sender_type' => Admin::MORPH_NAME,
                    'sender_id' => Admin::factory()
                        ->create()->id,
                ]
            );

        $this->postGraphQL(
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
                'sender_type' => Admin::MORPH_NAME,
                'is_read' => false,
            ]
        );
    }

    public function test_read_one_answer_support_request(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $supportRequest = $this->createSupportRequest($technician);

        $message1 = $supportRequest->messages()
            ->create(
                [
                    'message' => $this->faker->text,
                    'sender_type' => Admin::MORPH_NAME,
                    'sender_id' => Admin::factory()
                        ->create()->id,
                ]
            );

        $message2 = $supportRequest->messages()
            ->create(
                [
                    'message' => $this->faker->text,
                    'sender_type' => Admin::MORPH_NAME,
                    'sender_id' => Admin::factory()
                        ->create()->id,
                ]
            );

        $this->postGraphQL(
            GraphQLQuery::mutation(SupportRequestSetIsReadMutation::NAME)
                ->args(
                    [
                        'id' => $supportRequest->id,
                        'messages_ids' => [
                            $message1->id
                        ]
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

        $this->assertDatabaseHas(
            SupportRequestMessage::class,
            [
                'id' => $message1->id,
                'sender_type' => Admin::MORPH_NAME,
                'is_read' => true,
            ]
        );

        $this->assertDatabaseHas(
            SupportRequestMessage::class,
            [
                'id' => $message2->id,
                'sender_type' => Admin::MORPH_NAME,
                'is_read' => false,
            ]
        );
    }
}

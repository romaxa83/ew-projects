<?php

namespace Tests\Feature\Mutations\FrontOffice\Chat;

use App\GraphQL\Mutations\FrontOffice\Chat\SendMessageMutation;
use App\GraphQL\Queries\FrontOffice\Chat\Messages\MessageQuery;
use Core\Chat\Enums\MessageTypeEnum;
use Core\Chat\Events\ConversationStarted;
use Core\Chat\Events\MessageWasSent;
use Core\Chat\Events\ParticipantJoined;
use Core\Chat\Facades\Chat;
use Core\Chat\Models\Message;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SendMessageMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SendMessageMutation::NAME;

    public function test_send_message(): void
    {
        Event::fake();

        $technician = $this->loginAsTechnicianWithRole();

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'text' => $text = 'hello, dude!',
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertJsonPath('data.' . self::MUTATION, true);

        $this->assertAllDispatched();

        $conversation = Chat::conversations()
            ->getQueryForUser($technician)->first();

        $this->assertDatabaseHas(
            Message::TABLE,
            [
                'body' => $text,
                'conversation_id' => $conversation->id,
            ],
        );
    }

    public function test_send_message_with_attachments(): void
    {
        Event::fake();
        Storage::fake();

        $technician = $this->loginAsTechnicianWithRole();

        $query = GraphQLQuery::upload(self::MUTATION)
            ->args(
                [
                    'text' => $text = 'hello, dude!',
                    'attachments' => [
                        UploadedFile::fake()->image('test.png'),
                        UploadedFile::fake()->create('test.txt'),
                    ]
                ]
            )
            ->make();

        $this->postGraphQlUpload($query)
            ->assertJsonPath('data.' . self::MUTATION, true);

        $this->assertAllDispatched();

        $conversation = Chat::conversations()
            ->getQueryForUser($technician)->first();

        $this->assertDatabaseHas(
            Message::TABLE,
            [
                'body' => $text,
                'type' => MessageTypeEnum::ATTACHMENT,
                'conversation_id' => $conversation->id,
            ],
        );

        $message = Message::query()->where('body', $text)->first();

        self::assertCount(2, $message->media);

        $this->postGraphQL(
            GraphQLQuery::query(MessageQuery::NAME)
                ->args(
                    [
                        'conversation_id' => $conversation->id,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                            'type',
                            'attachments' => [
                                'name',
                                'mime_type'
                            ],
                        ],
                    ]
                )
                ->make()
        )
            ->assertJsonCount(2, 'data.' . MessageQuery::NAME . '.data.0.attachments');
    }

    protected function assertAllDispatched(): void
    {
        Event::assertDispatched(ConversationStarted::class);
        Event::assertDispatched(ParticipantJoined::class);
        Event::assertDispatched(MessageWasSent::class);
    }
}

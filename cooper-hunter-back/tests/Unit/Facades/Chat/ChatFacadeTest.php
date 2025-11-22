<?php

namespace Tests\Unit\Facades\Chat;

use App\Models\Admins\Admin;
use App\Models\Technicians\Technician;
use Core\Chat\Contracts\Messageable;
use Core\Chat\Events\ConversationStarted;
use Core\Chat\Events\ParticipantJoined;
use Core\Chat\Exceptions\InvalidDirectMessageNumberOfParticipants;
use Core\Chat\Facades\Chat;
use Core\Chat\Models\Conversation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;
use Tests\Traits\Chat\InteractsWithChatHelper;

class ChatFacadeTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithChatHelper;

    public function test_start_direct_conversation(): void
    {
        Event::fake();

        $admin = Admin::factory()->create();
        $technician = Technician::factory()->create();

        $conversation = Chat::conversation()
            ->participants($admin, $technician)
            ->direct()
            ->start();

        Event::assertDispatched(ConversationStarted::class);
        Event::assertDispatched(ParticipantJoined::class);

        self::assertEquals(2, $conversation->participants()->count());
    }

    public function test_participant_can_not_join_to_direct_chat(): void
    {
        Event::fake();

        $admin = Admin::factory()->create();
        $technician = Technician::factory()->create();

        $conversation = Chat::conversation()
            ->participants($admin, $technician)
            ->direct()
            ->start();

        $this->expectException(InvalidDirectMessageNumberOfParticipants::class);

        $conversation->addParticipants(Technician::factory()->create());
    }

    public function test_start_conversation_and_join_members(): void
    {
        Event::fake();

        $conversation = Chat::conversation()
            ->title('Conversation 1')
            ->description('Description for "Conversation 1"')
            ->start();

        Event::assertDispatched(ConversationStarted::class);
        Event::assertNotDispatched(ParticipantJoined::class);

        $conversation->addParticipants(Technician::factory()->create());
        $conversation->addParticipants(Technician::factory()->create());
        $conversation->addParticipants(Technician::factory()->create());

        Event::assertDispatched(ParticipantJoined::class);
    }

    public function test_unread_count(): void
    {
        Event::fake();

        $admin = Admin::factory()->create();

        $c = $this->createConversation($admin);
        $c->addParticipants($technician = Technician::factory()->create());

        //create 3 messages
        $this->sendMessage($admin, $c, 'message 1');
        $this->sendMessage($admin, $c, 'message 2');
        $this->sendMessage($admin, $c, 'message 3');

        //since the message is paginated for the sender, all messages are read
        $list = Chat::conversations()->paginateForUser($admin);
        self::assertEquals(0, $list->items()[0]->unread_messages_count);

        //because the technician hasn't read the messages yet
        $list = Chat::conversations()->paginateForUser($technician);
        self::assertEquals(3, $list->items()[0]->unread_messages_count);
    }

    public function test_read_messages(): void
    {
        Event::fake();

        $admin = Admin::factory()->create();

        $c = $this->createConversation($admin);
        $c->addParticipants($technician = Technician::factory()->create());

        $this->sendMessage($admin, $c, 'message 1');

        $messages = Chat::messages()
            ->forConversation($c)
            ->forParticipant($technician)
            ->paginate();

        self::assertEquals(false, $messages->items()[0]->messageNotifications[0]->is_seen);

        $ids = $messages->pluck('id')->toArray();

        $count = Chat::messages()
            ->forConversation($c)
            ->forParticipant($technician)
            ->markAsRead($ids);

        self::assertEquals(1, $count);

        $messages = Chat::messages()
            ->forConversation($c)
            ->forParticipant($technician)
            ->paginate();

        self::assertEquals(true, $messages->items()[0]->messageNotifications[0]->is_seen);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     */
    public function test_can_add_avatar_to_conversation(): void
    {
        Storage::fake();
        Event::fake();

        $conversation = Chat::conversation()->start();

        Chat::conversation($conversation)
            ->setAvatar(
                UploadedFile::fake()->image('test.png')
            );

        $this->assertInstanceOf(Media::class, $conversation->media[0]);
    }

    public function test_same_member_can_join_once(): void
    {
        Event::fake();

        $conversation = Chat::conversation()->start();

        $admin = Admin::factory()->create();

        $conversation->addParticipants($admin, $admin);

        self::assertEquals(1, $conversation->participants()->count());
    }

    public function test_count_messages_in_all_conversations(): void
    {
        Event::fake();

        $admin = Admin::factory()->create();
        $technician = Technician::factory()->create();

        $c1 = Chat::conversation()->start();
        $c2 = Chat::conversation()->start();
        $c3 = Chat::conversation()->start();

        $c1->addParticipants($admin, $technician);
        $c2->addParticipants($admin, $technician);
        $c3->addParticipants($admin, $technician);

        //read messages
        $this->sendQuickMessage($admin, $c1);
        $this->sendQuickMessage($admin, $c2);
        $this->sendQuickMessage($admin, $c3);

        //1 unread message in c1
        $this->sendQuickMessage($technician, $c1);

        //2 unread message in c2
        $this->sendQuickMessage($technician, $c2);
        $this->sendQuickMessage($technician, $c2);

        //3 unread message in c3
        $this->sendQuickMessage($technician, $c3);
        $this->sendQuickMessage($technician, $c3);
        $this->sendQuickMessage($technician, $c3);

        //total unread 6 messages
        $count = Chat::conversations()
            ->getUnreadCount($admin);

        self::assertEquals(6, $count);
    }

    protected function sendQuickMessage(Messageable $messageable, Conversation $conversation): void
    {
        Chat::message('message')
            ->from($messageable)
            ->to($conversation)
            ->send();
    }
}

<?php

namespace Tests\Unit\Repositories\Chat;

use App\Models\Admins\Admin;
use App\Models\Technicians\Technician;
use App\Repositories\Chat\Conversations\ConversationRepository;
use Core\Chat\Facades\Chat;
use Core\Chat\Models\Conversation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ConversationRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    protected ConversationRepository $repository;

    public function test_get_all(): void
    {
        $me = Admin::factory()->create();

        $this->createOtherConversation();
        $this->createMyConversation($me);
        $this->createNewConversation();

        $all = $this->repository->all($me);//only other

        self::assertCount(1, $all);
    }

    protected function createOtherConversation(): Conversation
    {
        return Chat::conversation()
            ->title('Conversation 1')
            ->description('Conversation belong to other admin')
            ->start()
            ->addParticipants(
                Admin::factory()->create(),
                Technician::factory()->create()
            );
    }

    protected function createMyConversation(Admin $me): Conversation
    {
        return Chat::conversation()
            ->title('Conversation 2')
            ->description('My conversation')
            ->start()
            ->addParticipants($me, Technician::factory()->create());
    }

    protected function createNewConversation(): Conversation
    {
        return Chat::conversation()
            ->title('Conversation 3')
            ->description('New conversation')
            ->start()
            ->addParticipants(Technician::factory()->create());
    }

    public function test_get_my(): void
    {
        $me = Admin::factory()->create();

        $this->createOtherConversation();
        $this->createMyConversation($me);
        $this->createNewConversation();

        $my = $this->repository->my($me);

        self::assertCount(1, $my);
    }

    public function test_get_new(): void
    {
        $me = Admin::factory()->create();

        $this->createOtherConversation();
        $this->createMyConversation($me);
        $this->createNewConversation();

        $new = $this->repository->new();

        self::assertCount(1, $new);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->repository = resolve(ConversationRepository::class);
    }
}

<?php

namespace Core\Chat\Services;

use Core\Chat\Contracts\Messageable;
use Core\Chat\Events\ConversationStarted;
use Core\Chat\Exceptions\ChatException;
use Core\Chat\Exceptions\DirectMessagingExistsException;
use Core\Chat\Facades\Chat;
use Core\Chat\Models\Conversation;
use Core\Chat\Repositories\ConversationRepository;
use Core\Chat\Traits\HasState;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class ConversationService
{
    use HasState;

    /** @var array<Messageable> */
    protected array $participants;
    protected bool $direct;

    protected string $title;
    protected string $description;
    protected ?Conversation $conversation;

    public function __construct(
        protected ConversationRepository $conversationRepository
    ) {
    }

    public function setConversation(Conversation $conversation = null): self
    {
        $this->conversation = $conversation;

        return $this;
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     */
    public function setAvatar(UploadedFile|string $file): void
    {
        if (empty($this->conversation)) {
            throw new ChatException('Conversation is not set.');
        }

        if ($file instanceof UploadedFile) {
            $this->conversation->addMedia($file)
                ->toMediaCollection($this->conversation::MEDIA_COLLECTION_NAME);
        } else {
            $this->conversation->addMediaFromUrl($file)
                ->toMediaCollection($this->conversation::MEDIA_COLLECTION_NAME);
        }
    }

    public function participants(Messageable|Collection|array $participants): self
    {
        if ($participants instanceof Messageable) {
            $participants = func_get_args();
        }

        foreach ($participants as $participant) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function direct(bool $direct = true): self
    {
        $this->direct = $direct;

        return $this;
    }

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function start(): Conversation
    {
        if ($this->shouldCreateDirect()) {
            $this->assertCanCreateDirect();
        }

        $conversation = $this->createConversation();

        $conversation->addParticipants($this->participants ?? []);

        event(new ConversationStarted($conversation));

        return $conversation;
    }

    protected function shouldCreateDirect(): bool
    {
        if (empty($this->direct)) {
            return false;
        }

        return $this->direct;
    }

    protected function assertCanCreateDirect(): void
    {
        if (empty($this->participants)) {
            throw new ChatException('Cannot create direct conversation. Participant list is empty.');
        }

        if (count($this->participants) !== 2) {
            throw new ChatException('Cannot create direct conversation. Number of recipients does not match.');
        }

        foreach ($this->participants as $participant) {
            if (!$participant instanceof Messageable) {
                throw new ChatException('Participant must be instance of Messageable interface');
            }
        }

        if (Chat::conversations()->between(...$this->participants)) {
            throw new DirectMessagingExistsException('Direct chatting already exists.');
        }
    }

    protected function createConversation(): Conversation
    {
        $conversation = new Conversation();
        $conversation->direct_message = $this->shouldCreateDirect();
        $conversation->title = $this->getTitle();
        $conversation->description = $this->getDescription();
        $conversation->save();

        return $conversation;
    }

    protected function getTitle(): ?string
    {
        return $this->title ?? null;
    }

    private function getDescription(): ?string
    {
        return $this->description ?? null;
    }

    public function close(): bool
    {
        $this->conversation->is_closed = true;
        $this->conversation->save();

        Chat::message('Current conversation ended')
            ->to($this->conversation)
            ->asNotification()
            ->markSeen()
            ->send();

        return true;
    }

    protected function propertiesToResetState(): array
    {
        return [
            'conversation',
            'participants',
            'direct',
            'title',
            'description',
        ];
    }
}

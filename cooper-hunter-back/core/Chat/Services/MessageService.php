<?php

namespace Core\Chat\Services;

use Core\Chat\Contracts\Messageable;
use Core\Chat\Entities\Messages\MessageMetaEntity;
use Core\Chat\Enums\MessageTypeEnum;
use Core\Chat\Exceptions\ChatException;
use Core\Chat\Jobs\SendMessageJob;
use Core\Chat\Models\Conversation;
use Core\Chat\Models\Message;
use Core\Chat\Models\Participation;
use Core\Chat\Repositories\MessageRepository;
use Core\Chat\Traits\HasState;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class MessageService
{
    use HasState;

    protected string $body;
    protected MessageTypeEnum $type;
    protected MessageMetaEntity $meta;

    protected Messageable $sender;
    protected Conversation $conversation;

    protected bool $markSeen;

    /**
     * @var UploadedFile[]
     */
    private array $attachments;

    public function __construct(
        protected MessageRepository $messageRepository
    ) {
    }

    /**
     * @param UploadedFile[] $attachments
     */
    public function attachments(array $attachments): static
    {
        $this->attachments = $attachments;

        return $this;
    }

    public function from(Messageable $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function to(Conversation $conversation): static
    {
        $this->conversation = $conversation;

        return $this;
    }

    public function body(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function send(): void
    {
        $this->assertCompleteMessage();

        SendMessageJob::dispatch($this->createMessage());
    }

    protected function assertCompleteMessage(): void
    {
        if (empty($this->sender) && $this->isSenderRequired()) {
            throw new ChatException('Message sender has not been set');
        }

        if (empty($this->body) && (empty($this->attachments))) {
            throw new ChatException('Message body has not been set');
        }

        if (empty($this->conversation)) {
            throw new ChatException('Message receiver has not been set');
        }
    }

    protected function isSenderRequired(): bool
    {
        return $this->getType()->in(
            [
                MessageTypeEnum::TEXT(),
                MessageTypeEnum::ATTACHMENT()
            ]
        );
    }

    public function getType(): MessageTypeEnum
    {
        if (!empty($this->type)) {
            return $this->type;
        }

        if (!empty($this->attachments) && count($this->attachments) > 0) {
            return MessageTypeEnum::ATTACHMENT();
        }

        return MessageTypeEnum::TEXT();
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    protected function createMessage(): Message
    {
        $this->conversation->touch();

        $seen = isset($this->markSeen) && $this->markSeen;

        $message = $this->conversation->messages()
            ->create(
                [
                    'body' => $this->body ?? null,
                    'participation_id' => $this->getSenderId(),
                    'type' => $this->getType(),
                    'mark_seen' => $seen,
                ]
            );

        $this->setAttachmentsToMessage($message);

        return $message;
    }

    public function getSenderId(): ?int
    {
        if (empty($this->sender)) {
            return null;
        }

        return $this->getParticipantFromSender($this->conversation, $this->sender)->getKey();
    }

    protected function getParticipantFromSender(Conversation $conversation, Messageable $sender): Participation
    {
        if (
            is_null(
                $participant = $conversation->participantFromSender($sender)
            )
        ) {
            throw new ChatException('Participant could not be found for Conversation');
        }

        return $participant;
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    protected function setAttachmentsToMessage(Message $message): void
    {
        if (!empty($this->attachments) && count($this->attachments) > 0) {
            if (is_array(reset($this->attachments))) {
                $attachments = array_merge(...$this->attachments);
            } else {
                $attachments = $this->attachments;
            }

            foreach ($attachments as $attachment) {
                $message->addMedia($attachment)
                    ->toMediaCollection();
            }
        }
    }

    /**
     * Determine if sent message should be already seen by receiver
     */
    public function markSeen(): self
    {
        $this->markSeen = true;

        return $this;
    }

    /**
     * Mark the message as notification
     */
    public function asNotification(): static
    {
        $this->type = MessageTypeEnum::NOTIFICATION();

        return $this;
    }

    /**
     * Mark the message as system
     */
    public function asSystem(): static
    {
        $this->type = MessageTypeEnum::SYSTEM();

        return $this;
    }

    protected function propertiesToResetState(): array
    {
        return [
            'body',
            'meta',
            'type',
            'sender',
            'conversation',
            'attachments',
            'markSeen',
        ];
    }
}

<?php

namespace Core\Chat\Repositories;

use Core\Chat\Contracts\Messageable;
use Core\Chat\Exceptions\ChatException;
use Core\Chat\Models\Conversation;
use Core\Chat\Models\Message;
use Core\Chat\Models\MessageNotification;
use Core\Chat\Services\MessageNotificationService;
use Core\Chat\Traits\HasState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageRepository
{
    use HasState;

    protected Conversation $conversation;
    protected Messageable $participant;

    protected array $filters;

    public function __construct(protected Message $model)
    {
    }

    public function forConversation(Conversation $conversation): self
    {
        $this->conversation = $conversation;

        return $this;
    }

    public function forParticipant(Messageable $messageable): self
    {
        $this->participant = $messageable;

        return $this;
    }

    public function filter(array $args): self
    {
        $this->filters = $args;

        return $this;
    }

    public function paginate(array $options = []): LengthAwarePaginator
    {
        $this->assertCompleteQuery();

        return $this->conversation
            ->messages()
            ->with(
                [
                    'messageNotifications' => fn(HasMany|MessageNotification $b) => $b
                        ->where('messageable_id', $this->participant->getKey())
                        ->where('messageable_type', $this->participant->getMorphClass())
                ]
            )
            ->with('media')
            ->filter($this->filters ?? [])
            ->orderByDesc('id')
            ->paginate(
                perPage: $options['per_page'] ?? 15,
                page: $options['page'] ?? 1
            );
    }

    protected function assertCompleteQuery(): void
    {
        if (($cEmpty = empty($this->conversation)) || ($pEmpty = empty($this->participant))) {
            $notSet = [];

            if ($cEmpty) {
                $notSet[] = '"conversation"';
            }

            if ($pEmpty ?? false) {
                $notSet[] = '"participant"';
            }

            $notSet = implode(' and ', $notSet);

            throw new ChatException("Chat messages could not be loaded, as $notSet is not set");
        }
    }

    /**
     * @param array $messageIds
     * @return int The number of read messages
     */
    public function markAsRead(array $messageIds): int
    {
        $this->assertCompleteQuery();

        return resolve(MessageNotificationService::class)
            ->markAsRead($this->conversation, $this->participant, $messageIds);
    }

    public function markAsReadAll(): int
    {
        $this->assertCompleteQuery();

        return resolve(MessageNotificationService::class)
            ->markAsReadAll($this->conversation, $this->participant);
    }

    protected function query(): Message|Builder
    {
        return $this->model->newQuery();
    }

    protected function propertiesToResetState(): array
    {
        return [
            'conversation',
            'participant',
            'filters',
        ];
    }
}

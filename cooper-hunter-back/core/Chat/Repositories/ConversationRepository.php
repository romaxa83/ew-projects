<?php

namespace Core\Chat\Repositories;

use Core\Chat\Contracts\Messageable;
use Core\Chat\Facades\Chat;
use Core\Chat\Models\Conversation;
use Core\Chat\Models\MessageNotification;
use Core\Chat\Models\Participation;
use Core\Chat\Traits\HasState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

class ConversationRepository
{
    use HasState;

    protected array $filters;

    public function __construct(protected Conversation $model)
    {
    }

    public function find(int $id): ?Conversation
    {
        return $this->query()->find($id);
    }

    protected function query(): Conversation|Builder
    {
        return $this->model->newQuery();
    }

    public function findOrFail(int $id): Conversation
    {
        return $this->query()->findOrFail($id);
    }

    public function findForUserOrFail(Messageable $participant, int $id): Conversation
    {
        return $this->getQueryForUser($participant)
            ->where(Chat::getConversationTable() . '.id', $id)
            ->firstOrFail();
    }

    public function getQueryForUser(Messageable $participant): Builder|Conversation
    {
        $query = $this->query()
            ->join(
                Chat::getParticipationTable() . ' as p',
                static fn(JoinClause $j) => $j
                    ->on(
                        Chat::getConversationTable() . '.id',
                        '=',
                        'p.conversation_id',
                    )
                    ->where('p.messageable_id', $participant->getKey())
                    ->where('p.messageable_type', $participant->getMorphClass())
            )
            ->with('lastMessage.participation.messageable')
            ->withCount(
                [
                    'notifications as unread_messages_count' => static fn(Builder|MessageNotification $b) => $b
                        ->where('messageable_id', $participant->getKey())
                        ->where('messageable_type', $participant->getMorphClass())
                        ->where('is_seen', false)
                ]
            );

        return $query
            ->addSelect(Chat::getConversationTable() . '.*')
            ->orderByDesc(Chat::getConversationTable() . '.updated_at')
            ->orderByDesc(Chat::getConversationTable() . '.id');
    }

    public function findForAdministratorOrFail(int $id): Conversation
    {
        return $this->getQueryForAdministrator()
            ->where(Chat::getConversationTable() . '.id', $id)
            ->firstOrFail();
    }

    public function getQueryForAdministrator(): Builder|Conversation
    {
        return $this->query()
            ->with('lastMessage.participation.messageable')
            ->addSelect(Chat::getConversationTable() . '.*')
            ->orderByDesc(Chat::getConversationTable() . '.updated_at');
    }

    public function filters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function paginateForUser(Messageable $participant): LengthAwarePaginator
    {
        return $this->getQueryForUser($participant)
            ->filter($this->getFilters())
            ->paginate();
    }

    protected function getFilters(): array
    {
        return $this->filters ?? [];
    }

    public function between(Messageable $m1, Messageable $m2): ?Conversation
    {
        return $this->query()
            ->whereHas(
                'participants', static fn(Builder|Participation $b) => $b
                ->where('messageable_id', $m1->getKey())
                ->where('messageable_type', $m1->getMorphClass())
            )
            ->whereHas(
                'participants', static fn(Builder|Participation $b) => $b
                ->where('messageable_id', $m2->getKey())
                ->where('messageable_type', $m2->getMorphClass())
            )
            ->first();
    }

    public function getUnreadCount(Messageable $participant): int
    {
        return $this->query()
            ->join(
                Chat::getMessageNotificationTable() . ' as noty',
                static fn(JoinClause $j) => $j
                    ->on(
                        Chat::getConversationTable() . '.id',
                        '=',
                        'noty.conversation_id',
                    )
                    ->where('noty.messageable_id', $participant->getKey())
                    ->where('noty.messageable_type', $participant->getMorphClass())
                    ->where('noty.is_seen', false)
            )
            ->count();
    }

    protected function propertiesToResetState(): array
    {
        return [
            'filters'
        ];
    }
}

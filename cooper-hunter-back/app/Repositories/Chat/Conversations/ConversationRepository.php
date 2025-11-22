<?php

namespace App\Repositories\Chat\Conversations;

use App\Models\Admins\Admin;
use Core\Chat\Facades\Chat;
use Core\Chat\Models\Participation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ConversationRepository
{
    protected ?string $search;

    public function search(?string $query): self
    {
        $this->search = $query;

        return $this;
    }

    public function allCount(Admin $admin): int
    {
        return $this->getAllQuery($admin)
            ->count();
    }

    protected function getAllQuery(Admin $admin): Builder
    {
        return Chat::conversations()
            ->getQueryForAdministrator()
            ->whereHas(
                'participants',
                static fn(Builder|Participation $b) => $b
                    ->where('messageable_type', (new Admin())->getMorphClass())
                    ->where('messageable_id', '<>', $admin->getKey())
            )
            ->filter($this->resolveFilters());
    }

    protected function resolveFilters(): array
    {
        if (isset($this->search)) {
            return [
                'search' => $this->search,
            ];
        }

        unset($this->search);

        return [];
    }

    public function all(Admin $admin): LengthAwarePaginator
    {
        return $this->getAllQuery($admin)
            ->paginate();
    }

    public function my(Admin $admin): LengthAwarePaginator
    {
        return $this->getMyQuery($admin)
            ->paginate();
    }

    protected function getMyQuery(Admin $admin): Builder
    {
        return Chat::conversations()
            ->getQueryForUser($admin)
            ->filter($this->resolveFilters());
    }

    public function myCount(Admin $admin): int
    {
        return $this->getMyQuery($admin)
            ->count();
    }

    public function new(): LengthAwarePaginator
    {
        return $this->getNewQuery()
            ->paginate();
    }

    protected function getNewQuery(): Builder
    {
        return Chat::conversations()
            ->getQueryForAdministrator()
            ->whereDoesntHave(
                'participants',
                static fn(Builder|Participation $b) => $b
                    ->where('messageable_type', (new Admin())->getMorphClass())
            )
            ->filter($this->resolveFilters());
    }

    public function newCount(): int
    {
        return $this->getNewQuery()
            ->count();
    }
}

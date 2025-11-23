<?php

namespace WezomCms\Users\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Users\Models\User;
use WezomCms\Users\Types\UserStatus;

class UserRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return User::query();
    }

    public function countByStatus(UserStatus $status): int
    {
        return $this->query()->where('status', $status->getValue())->count();
    }

    public function getInviters(): Collection
    {
        return $this->query()
            ->whereHas('referrals')
            ->withCount('referrals')
            ->get();
    }

    public function getForCollectionNotification(): Collection
    {
        return $this->query()
            ->whereNotNull('fcm_token')
            ->get();
    }
}

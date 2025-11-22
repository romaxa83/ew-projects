<?php

namespace App\Repositories\Forms;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Forms\Draft;
use App\Models\Users\User;

final readonly class DraftRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Draft::class;
    }

    public function getByUserAndPath(User $user, string $path)
    {
        return Draft::query()
            ->where('user_id', $user->id)
            ->where('path', $path)
            ->first();
    }
}

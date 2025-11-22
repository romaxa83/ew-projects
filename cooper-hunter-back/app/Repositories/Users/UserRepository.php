<?php

namespace App\Repositories\Users;

use App\Models\Users\User;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class UserRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return User::query();
    }
}

<?php

namespace WezomCms\Users\Repositories;

use App\Exceptions\UserNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Users\Models\User;

class UserRepository extends AbstractRepository
{
    protected function query()
    {
        return User::query();
    }

    /**
     * @param $phone
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|User|null
     * @throws \Exception
     */
    public function getByPhone($phone): ?User
    {
        $user = $this->query()
            ->where('phone', $phone)
            ->first();

        if (!$user){
            throw new UserNotFoundException(__('cms-users::site.exception.user not found by phone', ['phone' => $phone]));
        }

        return $user;
    }

    public function getUsersByIds(array $ids): Collection
    {
        return $this->query()->whereIn('id', $ids)->get();
    }
}

<?php

namespace App\Repositories\User;

use App\Models\User\User;
use App\Repositories\AbstractRepository;
use App\ValueObjects\Phone;

class UserRepository extends AbstractRepository
{
    public function query()
    {
        return User::query();
    }

    public function getByPhone(
        Phone $phone,
        $relation = [],
        string $withoutId = null): User|null
    {

        $query = $this->query()
            ->with($relation);

        if($withoutId){
            $query->where('id', '!=', $withoutId);
        }

        return $query->where('phone', $phone)->first();
    }

    public function getByPhoneWithTrashed(
        Phone $phone,
        $relation = [],
        string $withoutId = null): User|null
    {

        $query = $this->query()
            ->withTrashed()
            ->with($relation);

        if($withoutId){
            $query->where('id', '!=', $withoutId);
        }

        return $query->where('phone', $phone)->first();
    }

    public function getCountForDashboard(): int
    {
        return $this->query()->count();
    }

    public function existByEmail($email, $withoutId = null): bool
    {
        $q = $this->query()->withTrashed()->where('email', $email);

        if($withoutId){
           $q->where('id', '!=', $withoutId);
        }

        return $q->exists();
    }
}

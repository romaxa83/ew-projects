<?php

namespace App\Services\Users;

use App\Dto\Users\UserSyncDto;
use App\Models\Users\User;
use App\Repositories\Users\UserRepository;
use Carbon\CarbonImmutable;

class UserSyncService
{
    public function __construct(protected UserRepository $repo)
    {}

    public function createOrIgnore(UserSyncDto $dto)
    {
        make_transaction(function() use ($dto) {
            if(!$this->repo->getBy(['email' => $dto->email])){
                $this->create($dto);
            }
        });
    }

    public function create(UserSyncDto $dto): User
    {
        $model = $this->fill(new User(), $dto);
        $model->save();

        $model->roles()->sync([$dto->roleId]);

        return $model;
    }

    public function update(User $model, UserSyncDto $dto): User
    {
        $model = $this->fill($model, $dto);
        $model->save();

        $model->roles()->sync([$dto->roleId]);

        return $model;
    }

    protected function fill(User $model, UserSyncDto $dto): User
    {
        $model->origin_id = $dto->id;
        $model->first_name = $dto->firstName;
        $model->last_name = $dto->lastName;
        $model->second_name = $dto->secondName;
        $model->email = $dto->email;
        $model->status = $dto->status;
        $model->phone = $dto->phone;
        $model->phone_extension = $dto->phoneExtension;
        $model->phones = $dto->phones;
        $model->lang = $dto->lang;
        $model->password = $dto->password;
        if($model->password){
           $model->email_verified_at = CarbonImmutable::now();
        }
        $model->created_at = $dto->createdAt;
        $model->updated_at = $dto->updatedAt;
        $model->deleted_at = $dto->deletedAt;

        return $model;
    }
}


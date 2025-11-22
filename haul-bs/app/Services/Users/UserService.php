<?php

namespace App\Services\Users;

use App\Dto\Users\ProfileDto;
use App\Dto\Users\UserDto;
use App\Enums\Users\UserStatus;
use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(protected VerificationService $verificationService)
    {}


    public function create(UserDto $dto): User
    {
        return make_transaction(function() use ($dto) {

            $model = $this->fill(new User(), $dto);
            $model = $this->setStatus($model, UserStatus::PENDING());

            $model->save();

            $model->assignRole($dto->roleId);

            return $model;
        });
    }

    public function update(User $model, UserDto $dto): User
    {
        return make_transaction(function() use ($model, $dto) {

            $model = $this->fill($model, $dto);

            $model->save();

            $model->syncRoles($dto->roleId);

            return $model;
        });
    }

    public function updateProfile(User|Authenticatable $model, ProfileDto $dto): User
    {
        $model->first_name = $dto->firstName;
        $model->last_name = $dto->lastName;
        $model->phone = $dto->phone ?? $model->phone;
        $model->phones = $dto->phones ?? $model->phones;
        $model->phone_extension = $dto->phoneExtension ?? $model->phone_extension;
        $model->lang = $dto->lang ?? $model->lang;

        $model->save();

        return $model;
    }

    public function uploadAvatar(User $model, UploadedFile $file): User
    {
        $model = $this->deleteAvatar($model);
        $model->addImage($file);

        return $model;
    }

    public function deleteAvatar(User $model): User
    {
        $model->clearImageCollection();

        return $model;
    }

    private function fill(User $model, UserDto $dto): User
    {
        $model->first_name = $dto->firstName;
        $model->last_name = $dto->lastName;
        $model->email = $dto->email;
        $model->phone = $dto->phone;
        $model->phone_extension = $dto->phoneExtension;
        $model->phones = $dto->phones;
        $model->lang = $dto->lang;

        return $model;
    }

    public function setPassword(User $model , string $password, bool $save = false): User
    {
        $model->password = Hash::make($password);

        if($save){
            $model->save();
        }

        return $model;
    }

    public function setStatus(User $model , UserStatus $status, bool $save = false): User
    {
        $model->status = $status;

        if($save){
            $model->save();
        }

        return $model;
    }

    public function changeStatus(User $model): User
    {
        if($model->status->isActive()) return $this->setStatus($model, UserStatus::INACTIVE(), true);
        if($model->status->isInactive()) return $this->setStatus($model, UserStatus::ACTIVE(), true);

        return $model;
    }

    public function delete(User $model): bool
    {
        return $model->delete();
    }
}

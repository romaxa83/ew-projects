<?php

namespace App\Services\Users;

use App\Dto\Users\UserDto;
use App\Events\Members\MemberProfileDeletedEvent;
use App\Events\Users\UserRegisteredEvent;
use App\Models\Users\User;
use App\Services\Auth\PhoneAuthService;
use App\Traits\Auth\PasswordGenerator;
use Illuminate\Support\Collection;

class UserService
{
    use PasswordGenerator;

    public function __construct(protected PhoneAuthService $phoneAuthService)
    {
    }

    public function register(UserDto $dto): User
    {
        $user = $this->create($dto);

        event(new UserRegisteredEvent($user));

        return $user;
    }

    public function create(UserDto $dto): User
    {
        $user = new User();

        $this->fill($dto, $user);
        $user->setPassword($dto->getPassword());
        $user->save();

        return $user;
    }

    private function fill(UserDto $dto, User $user): void
    {
        if ($dto->hasSmsAccessToken()) {
            $this->phoneAuthService->confirmNewPhone($user, $dto->getSmsAccessToken());
        } else {
            $user->phone = $dto->getPhone();

            if ($user->isDirty('phone')) {
                $user->phone_verified_at = null;
            }
        }

        $user->first_name = $dto->getFirstName();
        $user->last_name = $dto->getLastName();
        $user->email = $dto->getEmail();
        $user->guid = $dto->getGuid();

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->setLanguage($dto->getLang());
    }

    public function update(User $user, UserDto $dto): User
    {
        $this->fill($dto, $user);

        if ($dto->hasPassword()) {
            $user->setPassword($dto->getPassword());
        }

        if ($dto->hasSmsAccessToken()) {
            $this->phoneAuthService->confirmNewPhone($user, $dto->getSmsAccessToken());
        }

        if ($user->isDirty()) {
            $user->save();
        }

        return $user;
    }

    public function changePassword(User $user, string $password): bool
    {
        return $user
            ->setPassword($password)
            ->save();
    }

    public function confirmPhone(User $user, string $smsAccessToken): bool
    {
        if ($this->phoneAuthService->confirmNewPhone($user, $smsAccessToken)) {
            return $user->save();
        }

        return false;
    }

    public function delete(Collection $users): bool
    {
        $users->each(
            function (User $user) {
                $this->deleteProfile($user, true);
            }
        );

        return true;
    }

    public function deleteProfile(User $user, bool $force = false): bool
    {
        if ($force) {
            $this->clearRelations($user);
        }

        event(new MemberProfileDeletedEvent($user));

        return $force ? $user->forceDelete() : $user->delete();
    }

    public function clearRelations(User $user): void
    {
        $user->projects()->delete();
        $user->alerts()->delete();
    }

    public function softDeleted(Collection $users): bool
    {
        $users->each(
            fn(User $user): bool => $this->deleteProfile($user)
        );

        return true;
    }

    public function restore(Collection $users): bool
    {
        $users->each(fn(User $user) => $user->restore());

        return true;
    }
}

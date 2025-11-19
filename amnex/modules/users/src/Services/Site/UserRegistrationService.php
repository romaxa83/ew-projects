<?php

namespace Wezom\Users\Services\Site;

use Wezom\Users\Dto\UserRegistrationDto;
use Wezom\Users\Events\Auth\UserRegisteredEvent;
use Wezom\Users\Models\User;

class UserRegistrationService
{
    public function register(UserRegistrationDto $dto): User
    {
        $user = $this->create($dto);

        event(new UserRegisteredEvent($user));

        return $user;
    }

    protected function create(UserRegistrationDto $dto): User
    {
        $user = new User();
        $this->fill($user, $dto);
        $user->save();

        return $user;
    }

    protected function fill(User $user, UserRegistrationDto $dto): void
    {
        $user->first_name = $dto->firstName;
        $user->last_name = $dto->lastName;
        $user->email = $dto->email;
        $user->setPassword($dto->password);
    }
}

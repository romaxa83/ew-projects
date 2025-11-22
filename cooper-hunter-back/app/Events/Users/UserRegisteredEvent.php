<?php

namespace App\Events\Users;

use App\Contracts\Alerts\AlertEvent;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Alerts\MetaDataDto;
use App\Contracts\Roles\HasGuardUser;
use App\Dto\Alerts\MetaData\UserDto;
use App\Models\Users\User;

class UserRegisteredEvent implements AlertEvent
{
    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getInitiator(): ?HasGuardUser
    {
        return null;
    }

    public function getModel(): AlertModel
    {
        return $this->user;
    }

    public function isAlertEvent(): bool
    {
        return true;
    }

    public function getMetaData(): ?MetaDataDto
    {
        return UserDto::fromEvent(['registration' => true]);
    }
}

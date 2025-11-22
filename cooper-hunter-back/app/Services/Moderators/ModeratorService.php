<?php

namespace App\Services\Moderators;

use App\Dto\Moderators\ModeratorDto;
use App\Models\OneC\Moderator;

class ModeratorService
{
    public function create(ModeratorDto $dto): Moderator
    {
        $moderator = new Moderator();
        $moderator->name = $dto->getName();
        $moderator->email = $dto->getEmail();
        $moderator->setPassword($dto->getPassword());

        $moderator->save();

        if ($dto->hasRoleId()) {
            $moderator->assignRole($dto->getRoleId());
        }

        return $moderator;
    }
}

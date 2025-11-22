<?php

namespace App\Permissions;

abstract class BasePermission implements Permission
{

    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'position' => $this->getPosition(),
        ];
    }

    public function getKey(): string
    {
        return static::KEY;
    }

}

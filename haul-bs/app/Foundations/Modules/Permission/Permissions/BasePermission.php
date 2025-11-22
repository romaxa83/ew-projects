<?php

namespace App\Foundations\Modules\Permission\Permissions;

readonly abstract class BasePermission implements Permission
{
    public function getKey(): string
    {
        return static::KEY;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'position' => $this->getPosition(),
        ];
    }

    public function getName(): string
    {
        return __('permissions.' . static::KEY);
    }

    public function getPosition(): int
    {
        return 0;
    }
}


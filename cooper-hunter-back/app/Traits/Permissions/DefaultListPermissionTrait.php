<?php

namespace App\Traits\Permissions;

use Illuminate\Support\Str;

trait DefaultListPermissionTrait
{
    public function canBeDeleted(): bool
    {
        return true;
    }

    public function canBeUpdated(): bool
    {
        return true;
    }

    public function getDeletePermissionKey(): string
    {
        return $this->getDefaultPermissionKey('delete');
    }

    protected function getDefaultPermissionKey(string $operation): string
    {
        $className = explode('\\', static::class);

        return sprintf(
            '%s.%s',
            Str::kebab(array_pop($className)),
            $operation
        );
    }

    public function getUpdatePermissionKey(): string
    {
        return $this->getDefaultPermissionKey('update');
    }
}

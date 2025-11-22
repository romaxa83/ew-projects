<?php

namespace App\Services\Permissions\Groups;

abstract class PermissionGroupAbstract
{
    public function mapsWithout(array $without = []): array
    {
        return [
            $this->getName() => array_diff($this->getPermissions(), $without)
        ];
    }

    abstract public function getName(): string;

    abstract public function getPermissions(): array;

    public function maps(): array
    {
        return [
            $this->getName() => $this->getPermissions()
        ];
    }
}

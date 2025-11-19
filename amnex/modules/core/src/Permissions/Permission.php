<?php

namespace Wezom\Core\Permissions;

use Wezom\Core\Contracts\Permissions\Permission as PermissionInterface;
use Wezom\Core\Enums\PermissionActionEnum;

class Permission implements PermissionInterface
{
    protected string $key;

    public function __construct(
        string $group,
        PermissionActionEnum|string $key,
        protected string $name,
        protected int $sort
    ) {
        $this->key = $group . '.' . ($key instanceof PermissionActionEnum ? $key->value : $key);
    }

    /**
     * {@inheritDoc}
     */
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
        return $this->key;
    }

    public function getName(): string
    {
        return __($this->name);
    }

    public function getPosition(): int
    {
        return $this->sort;
    }
}

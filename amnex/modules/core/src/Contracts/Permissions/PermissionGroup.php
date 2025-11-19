<?php

declare(strict_types=1);

namespace Wezom\Core\Contracts\Permissions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

interface PermissionGroup extends Arrayable
{
    public function getKey(): string;

    public function getName(): string;

    /**
     * @return Collection<Permission>
     */
    public function getPermissions(): Collection;

    public function getPosition(): int;
}

<?php

namespace App\Foundations\Modules\Permission\Roles;

interface DefaultRole
{
    public function getName(): string;

    public function getGuard(): string;

    public function getPermissions(): array;
}


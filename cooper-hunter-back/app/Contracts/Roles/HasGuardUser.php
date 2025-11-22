<?php

namespace App\Contracts\Roles;

use App\ValueObjects\Email;

interface HasGuardUser
{
    public const MIN_LENGTH_PASSWORD = 8;

    public function getMorphType(): string;

    public function getId(): int;

    public function getName(): string;

    public function getEmail(): Email;

    public function getGuardName(): string;
}

<?php

namespace App\Broadcasting\Channels;

use App\Models\Users\User;

interface Channel
{
    public static function getNameForUser(User $user): string;

    public function getPrefix(): string;

    public function getEvents(): array;

    public function isAllowedForUser(User $user): bool;
}

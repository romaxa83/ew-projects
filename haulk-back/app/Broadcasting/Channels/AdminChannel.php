<?php

namespace App\Broadcasting\Channels;

use App\Models\Admins\Admin;

interface AdminChannel
{
    public static function getNameForAdmin(Admin $admin): string;

    public function getPrefix(): string;

    public function getEvents(): array;

    public function isAllowedForAdmin(Admin $admin): bool;
}

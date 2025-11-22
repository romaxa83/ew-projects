<?php

namespace App\Events\Admins;

use App\Models\Admins\Admin;

class AdminCreatedEvent
{
    public function __construct(private Admin $admin, private string $password)
    {
    }

    public function getAdmin(): Admin
    {
        return $this->admin;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}

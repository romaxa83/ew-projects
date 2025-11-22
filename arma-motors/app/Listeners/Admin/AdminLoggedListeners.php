<?php

namespace App\Listeners\Admin;

use App\Events\Admin\AdminLogged;
use App\Models\Admin\Login;

class AdminLoggedListeners
{
    public function handle(AdminLogged $event)
    {
        try {
            Login::create([
                'admin_id' => $event->admin->id,
                'ip_address' => $event->ip,
                'created_at' => now()
            ]);

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}

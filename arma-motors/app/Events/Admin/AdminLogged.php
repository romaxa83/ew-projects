<?php

namespace App\Events\Admin;

use App\Models\Admin\Admin;
use Illuminate\Queue\SerializesModels;

class AdminLogged
{
    use SerializesModels;

    /**
     * AdminLogged constructor.
     * @param Admin $admin
     * @param string|null $ip
     */
    public function __construct(public Admin $admin, public ?string $ip = null)
    {}
}

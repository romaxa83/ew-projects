<?php

namespace App\Events\Admin;

use App\DTO\Admin\AdminDTO;
use App\Models\Admin\Admin;
use Illuminate\Queue\SerializesModels;

class GeneratePassword
{
    use SerializesModels;

    /**
     * AdminLogged constructor.
     * @param Admin $admin
     * @param string|null $ip
     */
    public function __construct(public AdminDTO $adminDTO)
    {}
}

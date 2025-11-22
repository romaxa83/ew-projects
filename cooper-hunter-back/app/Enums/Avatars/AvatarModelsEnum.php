<?php

namespace App\Enums\Avatars;

use App\Models\Admins\Admin;
use App\Models\Technicians\Technician;
use Core\Enums\BaseEnum;

class AvatarModelsEnum extends BaseEnum
{
    public const ADMIN = Admin::MORPH_NAME;
    public const TECHNICIAN = Technician::MORPH_NAME;
}

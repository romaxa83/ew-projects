<?php

namespace App\Enums\Avatars;

use App\Models\Admins\Admin;
use Core\Enums\BaseEnum;

class AvatarModelsEnum extends BaseEnum
{
    public const ADMIN = Admin::MORPH_NAME;
}

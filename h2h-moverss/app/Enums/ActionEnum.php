<?php

namespace App\Enums;

use App\Enums\Base\InvokableCases;

/**
 * @method static Create()
 * @method static Update()
 * @method static Delete()
 */

enum ActionEnum: string {

    use InvokableCases;

    case Create = "create";
    case Update = "update";
    case Delete = "delete";
}

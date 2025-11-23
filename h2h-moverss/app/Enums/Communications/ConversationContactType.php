<?php

namespace App\Enums\Communications;

use App\Enums\Base\InvokableCases;

/**
 * @method static Phone()
 * @method static Email()
 */

enum ConversationContactType: string {

    use InvokableCases;

    case Phone = "phone";
    case Email = "email";
}


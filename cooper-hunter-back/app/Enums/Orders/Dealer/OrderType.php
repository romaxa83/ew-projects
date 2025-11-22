<?php

namespace App\Enums\Orders\Dealer;

use Core\Enums\BaseEnum;

/**
 * @method static static ORDINARY()
 * @method static static STORAGE_RESPONSE()
 */
final class OrderType extends BaseEnum
{
    public const ORDINARY         = 'ordinary';
    public const STORAGE_RESPONSE = 'storage_response';
}

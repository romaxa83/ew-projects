<?php

namespace App\Enums\SupportRequests;

use Core\Enums\BaseEnum;

/**
 * Class OrderSubscriptionActionEnum
 * @package App\Enums\SupportRequests
 *
 * @method static static CREATED()
 * @method static static ADDED_MESSAGE()
 * @method static static CLOSED()
 */
final class SupportRequestSubscriptionActionEnum extends BaseEnum
{
    public const CREATED = 'created';
    public const ADDED_MESSAGE = 'added_message';
    public const CLOSED = 'closed';
}

<?php


namespace App\Enums\Clients;


use BenSampo\Enum\Contracts\LocalizedEnum;
use Core\Enums\BaseEnum;

/**
 * Class BanReasonsEnum
 * @package App\Enums\Clients
 *
 * @method static static NON_PAYMENT()
 * @method static static VIOLATIONS()
 */
class BanReasonsEnum extends BaseEnum implements LocalizedEnum
{
    public const NON_PAYMENT = 'NON_PAYMENT';
    public const VIOLATIONS = 'VIOLATIONS';
}

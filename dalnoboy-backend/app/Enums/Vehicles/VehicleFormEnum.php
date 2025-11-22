<?php


namespace App\Enums\Vehicles;


use Core\Enums\BaseEnum;

/**
 * Class VehicleFormEnum
 * @package App\Enums\Vehicles
 *
 * @method static static MAIN()
 * @method static static TRAILER()
 */
class VehicleFormEnum extends BaseEnum
{
    public const MAIN = 'main';
    public const TRAILER = 'trailer';
}

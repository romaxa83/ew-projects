<?php

namespace App\Enums\Solutions;

use BenSampo\Enum\Contracts\LocalizedEnum;
use Core\Enums\BaseEnum;

/**
 * Class SolutionClimateZoneEnum
 * @package App\Enums\Solutions
 *
 * @method static static AIR_HANDLER_UNIT()
 * @method static static CEILING_CASSETTE()
 * @method static static MINI_FLOOR_CONSOLE()
 * @method static static SLIM_DUCT()
 * @method static static UNIVERSAL_FLOOR_CEILING()
 * @method static static WALL_MOUNT()
 */
class SolutionIndoorEnum extends BaseEnum implements LocalizedEnum
{
    public const AIR_HANDLER_UNIT = 'AIR_HANDLER_UNIT';
    public const CEILING_CASSETTE = 'CEILING_CASSETTE';
    public const MINI_FLOOR_CONSOLE = 'MINI_FLOOR_CONSOLE';
    public const SLIM_DUCT = 'SLIM_DUCT';
    public const UNIVERSAL_FLOOR_CEILING = 'UNIVERSAL_FLOOR_CEILING';
    public const WALL_MOUNT = 'WALL_MOUNT';
}

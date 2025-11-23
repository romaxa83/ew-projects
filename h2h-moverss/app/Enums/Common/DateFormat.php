<?php

namespace App\Enums\Common;

use App\Enums\Base\InvokableCases;

/**
 * @method static ISO_8601()
 * @method static TZ_CHICAGO()
 * @method static FILTER_DATE()
 */

enum DateFormat: string {

    use InvokableCases;

    case ISO_8601 = 'Y-m-d\TH:i:s.u\Z';
    case TZ_CHICAGO = 'America/Chicago';
    case FILTER_DATE = 'Y-m-d';
}

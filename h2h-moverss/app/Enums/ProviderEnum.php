<?php

namespace App\Enums;

use App\Enums\Base\InvokableCases;

/**
 * @method static Zadarma()
 * @method static Ringostat()
 */

enum ProviderEnum: string {

    use InvokableCases;

    case Zadarma = "zadarma";
    case Ringostat = "ringostat";
}

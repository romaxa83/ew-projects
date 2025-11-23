<?php

namespace App\Enums\Communications;

use App\Enums\Base\InvokableCases;

/**
 * @method static Outbound()
 * @method static Inbound()
 * @method static Inner()
 */

enum Type: string {

    use InvokableCases;

    case Outbound = "outbound";     // исходящий, актуально для звонков, смс, email
    case Inbound  = "inbound";      // входящий, актуально для звонков, смс, email
    case Inner    = "inner";        // внутренние, актуально для какой-то активности

    public function isInbound(): bool
    {
        return $this->value === self::Inbound->value;
    }
}

<?php

namespace App\Enums\Orders\Parts;

use App\Foundations\Enums\Traits\InvokableCases;
use App\Foundations\Enums\Traits\Label;
use App\Foundations\Enums\Traits\RuleIn;

/**
 * @method static string Immediately()
 * @method static string Day_15()
 * @method static string Day_30()
 */
enum PaymentTerms: string {

    use InvokableCases;
    use RuleIn;
    use Label;

    case Immediately = 'immediately';
    case Day_15 = 'day_15';
    case Day_30 = 'day_30';

    public function label(): string
    {
        return match ($this->value){
            static::Immediately->value => 'Immediately',
            static::Day_15->value => '15 calendar days after delivery date',
            static::Day_30->value => '30 calendar days after delivery date',
            default => throw new \Exception('Unexpected match value'),
        };
    }

    public function isImmediately(): bool
    {
        return $this === self::Immediately;
    }

    public function isDay15(): bool
    {
        return $this === self::Day_15;
    }

    public function isDay30(): bool
    {
        return $this === self::Day_30;
    }
}

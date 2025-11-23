<?php

namespace App\Enums\Orders;

use App\Enums\Base\ForSelect;
use App\Enums\Base\InvokableCases;
use App\Enums\Base\Label;
use App\Enums\Base\RuleIn;

/**
 * @method static Local()
 * @method static Interstate()
 */

enum MoveTypeEnum: string
{
    use InvokableCases;
    use Label;
    use ForSelect;
    use RuleIn;

    case Local = "local";
    case Interstate = "interstate";

    public function label(): string
    {
        return match ($this->value){
            static::Local->value => 'Local/Intrastate',
            static::Interstate->value => 'Interstate',
        };
    }

    public function labelAsName(): string
    {
        return $this->label();
    }

    public function isLocal(): bool
    {
        return $this === self::Local;
    }
}

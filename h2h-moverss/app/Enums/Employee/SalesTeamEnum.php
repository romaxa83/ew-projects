<?php

namespace App\Enums\Employee;

use App\Enums\Base\ForSelect;
use App\Enums\Base\InvokableCases;
use App\Enums\Base\Label;
use App\Enums\Base\RuleIn;

/**
 * @method static Local()
 * @method static Local_long()
 */

enum SalesTeamEnum: string
{
    use InvokableCases;
    use Label;
    use ForSelect;
    use RuleIn;

    case Local = "local";
    case Local_long = "local_long";

    public function label(): string
    {
        return match ($this->value){
            static::Local->value => 'Local',
            static::Local_long->value => 'Local\Long',
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

    public function isLong(): bool
    {
        return $this === self::Local_long;
    }
}

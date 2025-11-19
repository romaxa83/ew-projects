<?php

declare(strict_types=1);

namespace Wezom\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;

abstract class BaseUniqueJob extends BaseJob implements ShouldBeUnique
{
    public function uniqueId(): string
    {
        return $this->getUniqueKey();
    }

    abstract protected function getUniqueKey(): string;
}

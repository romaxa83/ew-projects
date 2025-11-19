<?php

declare(strict_types=1);

namespace Wezom\Core\Contracts\Database\Seeders;

interface ConditionalSeeder
{
    public function shouldRun(): bool;
}

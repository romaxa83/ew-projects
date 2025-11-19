<?php

declare(strict_types=1);

namespace Wezom\Quotes\ModelFilters;

use Wezom\Core\ModelFilters\ModelFilter;
use Wezom\Quotes\Enums\QuoteStatusEnum;

class QuoteFilter extends ModelFilter
{
    public function user(int $value): void
    {
        $this->where('user_id', $value);
    }

    public function status(string|QuoteStatusEnum $status): void
    {
        if (is_string($status)) {
            $this->where('status', $status);

            return;
        }
        $this->where('status', $status->value);
    }
}

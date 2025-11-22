<?php

namespace App\Filters\Localization;

use App\Models\Localization\Translate;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SortFilterTrait;

class TranslateAdminFilter extends TranslateFilter
{
    use SortFilterTrait;
    use LikeRawFilterTrait;

    public function key(string $key): void
    {
        $this->likeRaw('key', $key);
    }

    public function text(string $text): void
    {
        $this->likeRaw('text', $text);
    }

    protected function allowedOrders(): array
    {
        return Translate::AVAILABLE_SORT_FIELDS;
    }
}

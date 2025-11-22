<?php

namespace App\Foundations\Modules\Localization\Filters;

use App\Foundations\Models\BaseModelFilter;
use App\Foundations\Modules\Localization\Models\Language;
use App\Foundations\Traits\Filters\ActiveFilter;

class LanguageFilter extends BaseModelFilter
{
    use ActiveFilter;

    protected function allowedOrders(): array
    {
        return Language::ALLOWED_SORTING_FIELDS;
    }
}


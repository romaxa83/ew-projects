<?php

namespace App\Traits\Localization;

trait LocalizationCacheTags
{
    protected function getCacheTags(array $args): array
    {
        return [
            'localization'
        ];
    }
}

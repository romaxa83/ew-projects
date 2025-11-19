<?php

declare(strict_types=1);

namespace Wezom\Core\ModelFilters;

use Wezom\Core\Enums\TranslationSideEnum;

class TranslationFilter extends ModelFilter
{
    public function side(string|TranslationSideEnum|array $side): void
    {
        if (is_array($side)) {
            $this->whereIn('side', $side);
        } else {
            $this->where('side', $side);
        }
    }

    public function key(string $key): void
    {
        $this->whereRaw(
            sprintf('LOWER(%s) LIKE ?', 'key'),
            ['%' . mb_strtolower($key) . '%']
        );
    }

    public function language(string $language): void
    {
        $this->where('language', $language);
    }
}

<?php

namespace App\Foundations\Modules\Localization\Filters;

use App\Foundations\Models\BaseModelFilter;
use App\Foundations\Modules\Localization\Models\Translation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TranslationFilter extends BaseModelFilter
{
    public function place(array|string $values): self
    {
        if(is_array($values)){
            return $this->whereIn('place', $values);
        }

        return $this->where('place', $values);
    }

    public function lang(array|string $values): self
    {
        if(is_array($values)){
            $values = array_map(
                static function ($item) {
                    return Str::lower($item);
                },
                $values
            );

            return $this->whereIn('lang', $values);
        }

        return $this->where('lang', $values);
    }

    public function key(string $value): void
    {
        $value = Str::lower($value);

        $this->where(
            fn(Builder $builder) => $builder->orWhereRaw('LOWER(`key`) LIKE ?', ["%$value%"])
        );
    }

    public function text(string $value): void
    {
        $value = Str::lower($value);

        $this->where(
            fn(Builder $builder) => $builder->orWhereRaw('LOWER(`text`) LIKE ?', ["%$value%"])
        );
    }

    protected function allowedOrders(): array
    {
        return Translation::ALLOWED_SORTING_FIELDS;
    }
}


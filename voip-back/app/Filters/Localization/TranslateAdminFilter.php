<?php

namespace App\Filters\Localization;

use App\Traits\Filter\SortFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TranslateAdminFilter extends TranslateSimpleFilter
{
    public function search(string $value): void
    {
        $value = Str::lower($value);

        $this->where(function (Builder $builder) use ($value) {
                $builder->orWhereRaw('LOWER(`key`) LIKE ?', ["%$value%"])
                    ->orWhereRaw('LOWER(`text`) LIKE ?', ["%$value%"])
                ;
            }
        );
    }

    public function key(string $key): void
    {
        $key = Str::lower($key);

        $this->where(
            function (Builder $builder) use ($key) {
                $builder->orWhereRaw('LOWER(`key`) LIKE ?', ["%$key%"]);
            }
        );
    }

    public function text(string $text): void
    {
        $text = Str::lower($text);

        $this->where(
            function (Builder $builder) use ($text) {
                $builder->orWhereRaw('LOWER(`text`) LIKE ?', ["%$text%"]);
            }
        );
    }
}

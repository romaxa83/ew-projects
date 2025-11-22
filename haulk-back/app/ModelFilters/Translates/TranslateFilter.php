<?php

namespace App\ModelFilters\Translates;

use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class TranslateFilter extends ModelFilter
{
    /**
     * @param string $text
     * @return TranslateFilter
     */
    public function text(string $text)
    {
        return $this->where(
            function (Builder $query) use ($text) {
                $query->whereRaw('lower(key) like ?', ['%' . escapeLike(mb_convert_case($text, MB_CASE_LOWER)) . '%'])
                    ->orWhereHas(
                        'data',
                        function (Builder $q) use ($text) {
                            $q->whereRaw('lower(text) like ?', ['%' . escapeLike(mb_convert_case($text, MB_CASE_LOWER)) . '%']);
                        }
                    );
            }
        );
    }
}

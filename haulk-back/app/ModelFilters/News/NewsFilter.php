<?php

namespace App\ModelFilters\News;

use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class NewsFilter extends ModelFilter
{
    /**
     * @param string $name
     * @return NewsFilter
     */
    public function name(string $name)
    {
        return $this->where(function (Builder $query) use ($name) {
            return $query
                ->whereRaw('lower(title_en) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                ->orWhereRaw('lower(title_ru) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                ->orWhereRaw('lower(title_es) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
        });
    }

    /**
     * @param string $date_from
     * @return NewsFilter
     */
    public function dateFrom(string $date_from): NewsFilter
    {
        return $this->where('created_at', '>=', $date_from . '00:00:00');
    }

    /**
     * @param string $date_to
     * @return NewsFilter
     */
    public function dateTo(string $date_to): NewsFilter
    {
        return $this->where('created_at', '<=', $date_to . '23:59:59');
    }
}

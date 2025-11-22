<?php


namespace App\ModelFilters\Locations;


use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class StateFilter extends ModelFilter
{
    /**
     * @param bool $status
     * @return StateFilter
     */
    public function status(bool $status)
    {
        return $this->where('status', '=', $status);
    }

    /**
     * @param string $name
     * @return StateFilter
     */
    public function name(string $name)
    {
        return $this->where(function (Builder $query) use ($name) {
            return $query
                ->whereRaw('lower(name) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                ->orWhereRaw('lower(state_short_name) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
        });
    }
}

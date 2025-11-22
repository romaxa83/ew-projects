<?php


namespace App\ModelFilters\Locations;


use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class CityFilter extends ModelFilter
{
    /**
     * @param bool $status
     * @return CityFilter
     */
    public function status(bool $status)
    {
        return $this->where('status', '=', $status);
    }

    /**
     * @param string $name
     * @return CityFilter
     */
    public function name(string $name)
    {
        return $this->whereRaw('lower(name) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
    }

    /**
     * @param int $stateId
     * @return CityFilter
     */
    public function stateId(int $stateId)
    {
        return $this->where('state_id', '=', $stateId);
    }

    /**
     * @param string $name
     * @return CityFilter
     */
    public function s(string $name)
    {
        return $this->whereRaw('lower(name) like ?', [escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
    }

    /**
     * @param string $zip
     * @return CityFilter
     */
    public function zip(string $zip)
    {
        return $this->whereRaw('lower(zip) like ?', [escapeLike(mb_convert_case($zip, MB_CASE_LOWER)) . '%']);
    }
}

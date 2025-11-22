<?php


namespace App\ModelFilters\Library;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Carbon;

class LibraryDocumentFilter extends ModelFilter
{

    /**
     * @param string $date_from
     * @return LibraryDocumentFilter
     */
    public function dateFrom(string $date_from)
    {
        return $this->where('created_at', '>=', Carbon::make($date_from)->startOfDay());
    }

    /**
     * @param string $date_to
     * @return LibraryDocumentFilter
     */
    public function dateTo(string $date_to)
    {
        return $this->where('created_at', '<=', Carbon::make($date_to)->endOfDay());
    }

    /**
     * @param string $name
     * @return LibraryDocumentFilter
     */
    public function name(string $name)
    {
        return $this->whereRaw('lower(name) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
    }

    /**
     * @param string $name
     * @return LibraryDocumentFilter
     */
    public function s(string $name)
    {
        return $this->whereRaw('lower(name) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
    }

    /**
     * @param string $driver_id
     * @return LibraryDocumentFilter
     */
    public function driver(string $driver_id)
    {
        return $this->where('user_id', intval($driver_id));
    }
}

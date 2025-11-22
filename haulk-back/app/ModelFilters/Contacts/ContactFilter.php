<?php

namespace App\ModelFilters\Contacts;

use App\Models\Orders\Order;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class ContactFilter extends ModelFilter
{
    /**
     * @param string $name
     * @return ContactFilter
     */
    public function name(string $name)
    {
        return $this->where(function (Builder $query) use ($name) {
            return $query
                ->whereRaw('lower(full_name) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                ->orWhereRaw('lower(phone_name) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                ->orWhereRaw('lower(address) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
        });
    }

    /**
     * @param string $type
     * @return ContactFilter
     */
    public function type($type_id)
    {
        return $this->where('type_id', intval($type_id));
    }

    /**
     * @param string $name
     * @return ContactFilter
     */
    public function s(string $name)
    {
        return $this->whereRaw('lower(full_name) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
    }
}

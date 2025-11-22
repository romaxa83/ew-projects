<?php

namespace App\Foundations\Modules\Location\Filters;

use App\Foundations\Models\BaseModelFilter;
use App\Foundations\Modules\Location\Models\State;
use App\Foundations\Traits\Filters\ActiveFilter;
use Illuminate\Database\Eloquent\Builder;

class StateFilter extends BaseModelFilter
{
    use ActiveFilter;

    protected function allowedOrders(): array
    {
        return State::ALLOWED_SORTING_FIELDS;
    }

    public function name(string $name)
    {
        return $this->where(function (Builder $query) use ($name) {
            return $query
                ->whereRaw('lower(name) like ?', ['%' . escape_like(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                ->orWhereRaw('lower(state_short_name) like ?', ['%' . escape_like(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
        });
    }
}

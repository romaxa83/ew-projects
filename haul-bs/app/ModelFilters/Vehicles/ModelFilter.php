<?php

namespace App\ModelFilters\Vehicles;

use App\Foundations\Models\BaseModelFilter;
use App\Models\Vehicles\Make;
use Illuminate\Database\Eloquent\Builder;

class ModelFilter extends BaseModelFilter
{

    public function makeName(string $value)
    {
        return $this->where(function (Builder $query) use ($value) {
            $query->whereHas('make',
                function (Builder $q) use ($value) {
                    $q->whereRaw(
                        'lower(' . Make::TABLE . '.name) like ?',
                        ['%' . escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%']
                    );
                }
            );
        });
    }

    public function search(string $value): self
    {
        return $this->whereRaw('lower(name) like ?', ['%' . escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%']);
    }
}

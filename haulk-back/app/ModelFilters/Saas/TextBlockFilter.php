<?php 

namespace App\ModelFilters\Saas;

use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class TextBlockFilter extends ModelFilter
{

    public function query(string $query): TextBlockFilter
    {
        return $this->where("block", "ILIKE", "%{$query}%")
            ->orWhere("en", "ILIKE", "%{$query}%")
            ->orWhere("es", "ILIKE", "%{$query}%")
            ->orWhere("ru", "ILIKE", "%{$query}%");
    }

    public function group(?int $group): TextBlockFilter
    {
        return $this->where("group", "=", $group);
    }

    public function scope(array $scopes): TextBlockFilter
    {
        return $this->where(
            function (Builder $builder) use ($scopes) {
                $builder->whereRaw("scope ??& array['" . $scopes[0] . "']");

                unset($scopes[0]);

                if (empty($scopes)) {
                    return;
                }

                foreach ($scopes as $scope) {
                    $builder->orWhereRaw("scope ??& array['" . $scope . "']");
                }
            }
        );
    }
}

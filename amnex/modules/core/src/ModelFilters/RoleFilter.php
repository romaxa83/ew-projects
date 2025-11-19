<?php

declare(strict_types=1);

namespace Wezom\Core\ModelFilters;

use Illuminate\Database\Eloquent\Builder;

class RoleFilter extends ModelFilter
{
    public function name(string $name): void
    {
        $name = strtolower($name);

        $this->whereRaw('LOWER(name) LIKE ?', ["%$name%"]);
    }

    public function exclude(array $exclude): void
    {
        $this->where(
            fn (Builder $builder) => $builder->whereNotIn('system_type', $exclude)
                ->orWhereNull('system_type')
        );
    }
}

<?php

declare(strict_types=1);

namespace Wezom\Admins\ModelFilters;

use Illuminate\Database\Eloquent\Builder;
use Str;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\Models\Admin;
use Wezom\Core\ModelFilters\ModelFilter;
use Wezom\Core\Models\Permission\Role;

class AdminFilter extends ModelFilter
{
    public function query(string $query): void
    {
        $query = Str::lower($query);

        $this->where(
            function (Builder $builder) use ($query) {
                $table = (new Admin())->getTable();

                $builder
                    ->orWhereRaw(sprintf('LOWER(%s) LIKE ?', "$table.email"), "%$query%")
                    ->orWhereRaw(sprintf('LOWER(%s) LIKE ?', "$table.phone"), "%$query%")
                    ->orWhere(function (Builder $builder) use ($query, $table) {
                        foreach (array_filter(preg_split('/\s/', $query)) as $word) {
                            $builder->orWhereRaw(sprintf('LOWER(%s) LIKE ?', "$table.first_name"), "%$word%")
                                ->orWhereRaw(sprintf('LOWER(%s) LIKE ?', "$table.last_name"), "%$word%");
                        }
                    });
            }
        );
    }

    public function roleIds(array $values): void
    {
        $this->whereHas('roles', fn (Builder|Role $builder) => $builder->whereIn('id', $values));
    }

    public function position(string $position): void
    {
        $this->where('position', $position);
    }

    public function status(string|AdminStatusEnum $status): void
    {
        $this->where('status', $status->value);
    }

    /**
     * @see getCustomMethodName
     */
    public function customFullNameSort(string $direction): void
    {
        $this->orderByRaw('CONCAT(first_name, last_name) ' . strtoupper($direction));
    }
}

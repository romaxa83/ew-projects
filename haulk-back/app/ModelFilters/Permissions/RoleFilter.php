<?php

namespace App\ModelFilters\Permissions;

use EloquentFilter\ModelFilter;

class RoleFilter extends ModelFilter
{
    public function id(int $id): void
    {
        $this->whereKey($id);
    }

    public function name(string $name): void
    {
        $name = strtolower($name);

        $this->orWhereRaw('LOWER(name) LIKE ?', ["%$name%"]);
    }
}

<?php

namespace Tests\Helpers\Traits;

use App\Models\Admins\Admin;
use Illuminate\Database\Eloquent\Collection;

trait AdminFactory
{
    public function createAdmin(array $attrs = []): Admin
    {
        return factory(Admin::class)->create($attrs);
    }

    public function createAdmins(int $times, array $attrs = []): Collection
    {
        return factory(Admin::class)
            ->times($times)
            ->create($attrs);
    }
}

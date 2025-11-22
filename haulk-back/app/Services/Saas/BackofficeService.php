<?php


namespace App\Services\Saas;


use App\Models\Admins\Admin;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;

class BackofficeService
{
    public function getSuperAdmin(): Admin
    {
        return Admin::whereHas(
            'roles',
            function (Builder $builder) {
                $builder->where(
                    [
                        'name' => User::SUPERADMIN_ROLE,
                        'guard_name' => Admin::GUARD,
                    ]
                );
            }
        )->first();
    }
}

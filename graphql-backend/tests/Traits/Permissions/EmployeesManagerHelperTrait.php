<?php

namespace Tests\Traits\Permissions;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyUser;
use App\Models\Users\User;
use App\Permissions\Employees\EmployeeCreatePermission;
use App\Permissions\Employees\EmployeeDeletePermission;
use App\Permissions\Employees\EmployeeListPermission;
use App\Permissions\Employees\EmployeeUpdatePermission;

trait EmployeesManagerHelperTrait
{
    protected function loginAsEmployeesManager(User $user = null, bool $isOwner = false): User
    {
        $user = $this->loginAsUser($user, $isOwner)
            ->assignRole(
                $this->generateRole(
                    'Employees manager',
                    [
                        EmployeeListPermission::KEY,
                        EmployeeCreatePermission::KEY,
                        EmployeeUpdatePermission::KEY,
                        EmployeeDeletePermission::KEY
                    ]
                )
            );

        CompanyUser::query()->upsert(
            [
                'user_id' => $user->id,
                'company_id' => $user->company->id,
                'state' => Company::STATE_OWNER,
            ],
            ['user_id', 'company_id']
        );

        return $user;
    }
}

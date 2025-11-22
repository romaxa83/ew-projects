<?php


namespace Tests\Traits\Permissions;

use App\Models\Admins\Admin;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyUser;
use App\Models\Users\User;
use App\Permissions\Companies\CompanyUpdatePermission;

trait CompanyManagerHelperTrait
{
    use RoleHelperHelperTrait;

    protected function loginAsCompanyManager(): User
    {
        $user = $this->loginAsUser()
            ->assignRole(
                $this->generateRole(
                    'Company manager',
                    [
                        CompanyUpdatePermission::KEY,
                    ]
                )
            );

        $user->company()->update(
            [
                'lang' => 'uk'
            ]
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

    protected function loginAsCompanyAdminManager(): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole
            (
                $this->generateRole(
                    'Company Admin',
                    [
                        CompanyUpdatePermission::KEY,
                    ],
                    Admin::GUARD,
                )
            );
    }
}

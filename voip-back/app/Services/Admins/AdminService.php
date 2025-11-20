<?php

namespace App\Services\Admins;

use App\Dto\Admins\AdminDto;
use App\Dto\Admins\AdminProfileDto;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Repositories\Admins\AdminRepository;
use App\Repositories\Permissions\RoleRepository;
use App\Services\AbstractService;
use App\Services\Auth\AdminPassportService;
use App\Services\Localizations\LocalizationService;
use Carbon\CarbonImmutable;
use Core\Exceptions\TranslatedException;
use Illuminate\Contracts\Auth\Authenticatable;

class AdminService extends AbstractService
{
    public function __construct(
        private LocalizationService $localizationService,
        protected AdminPassportService $passportService,
        protected RoleRepository $roleRepository
    )
    {
        $this->repo = resolve(AdminRepository::class);
        return parent::__construct();
    }

    public function create(AdminDto $dto): Admin
    {
        // нельзя создать второго супер админа
        /** @var $role Role */
        $role = $this->roleRepository->getBy('id', $dto->getRoleId());
        if($role && $role->isSuperAdmin()){
            if($this->repo->hasAdminWithRole($role)){
                throw new TranslatedException(__('exceptions.admin.cant_create_super_admin'));
            }
        }

        $admin = new Admin();
        $admin->name = $dto->getName();
        $admin->email = $dto->getEmail();
        $admin->email_verified_at = CarbonImmutable::now();
        $admin->setPassword($dto->getPassword());

        $admin->lang = $this->localizationService->getDefaultSlug();
        $admin->save();

        if($dto->hasRoleId()){
            $admin->assignRole($dto->getRoleId());
        }

        return $admin;
    }

    public function update(Admin $admin, AdminDto $dto): Admin
    {
        $admin->name = $dto->getName();
        $admin->email = $dto->getEmail();

        if ($dto->hasPassword()) {
            $admin->setPassword($dto->getPassword());
        }

        if ($dto->hasRoleId()) {
            // супер-админ не может сменить себе роль
            if($admin->isSuperAdmin() && $admin->roles[0]->id !== $dto->getRoleId()){
                throw new TranslatedException(__('exceptions.admin.cant_change_role_as_super_admin'));
            }
            $admin->syncRoles($dto->getRoleId());
        }

        if ($admin->getDirty()) {
            $admin->save();
        }

        return $admin;
    }

    public function updateProfile(Admin $model, AdminProfileDto $dto): Admin
    {
        if($dto->name){
            $model->name = $dto->name;
        }
        if($dto->email){
            $model->email = $dto->email;
        }
        if($dto->password) {
            $model->setPassword($dto->password);
        }

        if ($model->getDirty()) {
            $model->save();
        }

        return $model;
    }

    public function deactivate(Admin $model): void
    {
        $model->active = false;
        $model->save();

        $this->passportService->logout($model);
    }

    public function activate(Admin $model): void
    {
        $model->active = true;
        $model->save();
    }
}

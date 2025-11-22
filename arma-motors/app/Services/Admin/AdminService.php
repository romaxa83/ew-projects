<?php

namespace App\Services\Admin;

use App\DTO\Admin\AdminDTO;
use App\DTO\Admin\AdminEditDTO;
use App\Models\Admin\Admin;
use App\Services\Localizations\LocalizationService;
use DB;
use Exception;

class AdminService
{
    public function __construct(private LocalizationService $localizationService)
    {}

    public function create(AdminDTO $dto): Admin
    {
        DB::beginTransaction();
        try {

            $admin = new Admin();
            $admin->name = $dto->getName();
            $admin->email = $dto->getEmail();
            $admin->phone = $dto->getPhone();
            $admin->setPassword($dto->getPassword());
            $admin->lang = $this->localizationService->getDefaultSlugByAdmin();
            $admin->dealership_id = $dto->getDealershipId();
            $admin->department_type = $dto->getDepartmentType();
            $admin->service_id = $dto->getServiceId();

            $admin->save();

            if($dto->hasRole()){
                $admin->assignRole($dto->getRole());
            }

            DB::commit();

            return $admin;

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(AdminEditDTO $dto, Admin $admin): Admin
    {
        DB::beginTransaction();
        try {
            $admin->assetSuperAdmin();

            $admin->name = $dto->changeName() ? $dto->getName() : $admin->name;
            $admin->email = $dto->changeEmail() ? $dto->getEmail() : $admin->email;
            $admin->phone = $dto->changePhone() ? $dto->getPhone() : $admin->phone;
            $admin->dealership_id = $dto->changeDealershipId()
                ? $dto->getDealershipId()
                : $admin->dealership_id;
            $admin->department_type = $dto->changeDepartmentType()
                ? $dto->getDepartmentType()
                : $admin->department_id;
            $admin->service_id = $dto->changeServiceId()
                ? $dto->getServiceId()
                : $admin->service_id;
//            $admin->lang = $dto->changeLang() ? $dto->getLang() : $admin->lang;

            if($dto->changeRole()){
                $admin->deleteExistRoles();
                if($dto->hasRole()){
                    $admin->assignRole($dto->getRole());
                }
            }

            $admin->save();

            DB::commit();

            return $admin;

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function changeStatus(Admin $admin, $status): Admin
    {
        try {
            $admin->assetSuperAdmin();

            $admin->status = $status;
            $admin->save();

            return $admin;

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function updatePassword(Admin $admin, string $password): Admin
    {
        try {
            $admin->assetSuperAdmin();

            $admin->setPassword($password);
            $admin->save();

            return $admin;

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param Admin $admin
     * @return bool
     * @throws Exception
     */
    public function delete(Admin $admin): bool
    {
        try {
            $admin->assetSuperAdmin();

            return $admin->delete();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function restore(Admin $admin): Admin
    {
        try {
            if(!$admin->trashed()){
                throw new \Exception(__('error.model not trashed'));
            }

            $admin->restore();

            return $this->changeStatus($admin, Admin::STATUS_ACTIVE);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}

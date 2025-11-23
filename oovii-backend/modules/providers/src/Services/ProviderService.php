<?php

namespace WezomCms\Providers\Services;

use DB;
use Exception;
use Hash;
use Throwable;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Models\Role;
use WezomCms\Core\Repositories\AdminRepository;
use WezomCms\Providers\Dto\ProviderDto;
use WezomCms\Providers\Models\Provider;

class ProviderService
{

    public function __construct(
        protected AdminRepository $adminRepository
    )
    {}

    public function create(ProviderDto $dto): Provider
    {
        DB::beginTransaction();

        try {
            $model = new Provider();
            $model->name = $dto->name;
            $model->email = $dto->email;
            $model->password = Hash::make($dto->password);
            $model->phone = $dto->phone;
            $model->company = $dto->company;
            $model->region_code = $dto->regionCode;
            $model->city_code = $dto->cityCode;
            $model->address = $dto->address;

            $model->save();

            DB::commit();

            return $model;
        } catch (Throwable $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }

    public function createAdminProfile(Provider $model): Provider
    {
        DB::beginTransaction();

        try {
            if($model->adminProfile) {

                $model->adminProfile->name = $model->name;
                $model->adminProfile->password = $model->password;
                $model->adminProfile->email = $model->email;
                $model->adminProfile->active = $model->active;
                if($model->isDraft()){
                    $model->adminProfile->active = false;
                }
                $model->adminProfile->save();

                DB::commit();

                return $model;
            }

            $admin = new Administrator();
            $admin->name = $model->name;
            $admin->password = $model->password;
            $admin->email = $model->email;
            $admin->active = true;
            $admin->super_admin = false;
            $admin->notify = true;
            $admin->save();

            $role = Role::query()->where('name', Role::DEFAULT_PROVIDER)->first();
            if(!$role){
                throw new Exception("Not found role [moderator]");
            }

            $admin->roles()->attach($role);

            $model->admin_id = $admin->id;

            $model->save();

            DB::commit();

            return $model;
        } catch (Throwable $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}

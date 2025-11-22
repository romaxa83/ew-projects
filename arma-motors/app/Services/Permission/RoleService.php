<?php

namespace App\Services\Permission;

use App\DTO\Permission\RoleDTO;
use App\DTO\Permission\RoleTranslationDTO;
use App\Models\Admin\Admin;
use App\Models\Permission\Role;
use App\Models\Permission\RoleTranslation;
use App\Traits\Translations\TranslationCrud;
use DB;

class RoleService
{
    use TranslationCrud;

    public function __construct()
    {}

    public function create(RoleDTO $dto, string $guardName = Admin::GUARD): Role
    {
        DB::beginTransaction();
        try {

            $model = new Role();
            $model->name = $dto->getName();
            $model->guard_name = $guardName;
            $model->save();

            foreach ($dto->getTranslations() as $translation)
            {
                /** @var $translation RoleTranslationDTO */
                $t = new RoleTranslation();
                $t->name = $translation->getName();
                $t->lang = $translation->getLang();
                $t->role_id = $model->id;
                $t->save();
            }

            DB::commit();

            return $model;

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function update(RoleDTO $dto, Role $model): Role
    {
        DB::beginTransaction();
        try {

            $this->editName($model, $dto);

            DB::commit();

            return $model;

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function delete(Role $model)
    {
        try {
            return $model->delete();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function attachPermissions(Role $model, array $perms): Role
    {
        try {
            $model->syncPermissions($perms);

            return $model;

        } catch (\Throwable $e) {

            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

}

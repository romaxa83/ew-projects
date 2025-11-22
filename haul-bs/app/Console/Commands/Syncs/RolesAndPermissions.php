<?php

namespace App\Console\Commands\Syncs;

use App\Foundations\Modules\Permission\Actions\Roles\RoleCreateAction;
use App\Foundations\Modules\Permission\Actions\Roles\RoleUpdateAction;
use App\Foundations\Modules\Permission\Dto\RoleDto;
use App\Foundations\Modules\Permission\Models\Permission;
use App\Foundations\Modules\Permission\Models\Role;
use App\Foundations\Modules\Permission\Roles\DefaultRole;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class RolesAndPermissions extends Command
{
    protected $signature = 'sync:role_and_perm';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            $this->info("Done [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function exec()
    {
        $this->createDefaultRoles();
    }

    private function createDefaultRoles()
    {
        // получаем все пермишины из бд
        $perms = Permission::all();

        foreach (Role::defaultRoles() as $class){

            if(!class_exists($class)) continue;

            /** @var DefaultRole $defaultRole */
            $defaultRole = resolve($class);
            /** @var Role $role */
            $role = Role::query()->where([
                ['name', $defaultRole->getName()],
                ['guard_name', $defaultRole->getGuard()]
            ])->first();

            $dto = RoleDto::byArgs([
                'name' => $defaultRole->getName(),
                'guard' => $defaultRole->getGuard(),
                'permission_ids' => $this->permissionIdsForRole(
                    $defaultRole,
                    $perms
                ),
            ]);

            if($role){
                /** @var $command RoleUpdateAction */
                $command = resolve(RoleUpdateAction::class);
                $command->exec($role, $dto);
            } else {
                /** @var $command RoleCreateAction */
                $command = resolve(RoleCreateAction::class);
                $command->exec($dto);
            }

            $this->info("Create role [{$defaultRole->getName()}] by guard [{$defaultRole->getGuard()}]");

        }
    }

    private function permissionIdsForRole(
        DefaultRole $defaultRole,
        Collection $permissions,
    ): array
    {
        // получаем прописанные пермишины для роли
        $permissionsForRole = [];
        foreach ($defaultRole->getPermissions() as $permissionClasses){
            foreach ($permissionClasses as $permissionClass){
                $permissionsForRole[] = $permissionClass::KEY;
            }
        }

        // получаем из бд те пермишены относятся к этой роли
        $permissionDB = $permissions
            ->where('guard_name', $defaultRole->getGuard())
            ->whereIn('name', $permissionsForRole)
            ->pluck('name', 'id')
            ->toArray()
        ;

        // получаем расхождение, т.е. пермишены для роли которых нет в бд
        $permDiff = array_diff($permissionsForRole, $permissionDB);
        if(!empty($permDiff)){
            // создаем эти пермишены
            $upsertData = [];
            foreach ($permDiff as $item){
                $upsertData[] = [
                    'guard_name' => $defaultRole->getGuard(),
                    'name' => $item,
                    'group' => current(explode('.', $item)),
                ];
            }

            Permission::query()->upsert($upsertData, ['name', 'guard_name']);

            $newPerms = Permission::query()
                ->where('guard_name', $defaultRole->getGuard())
                ->whereIn('name', $permDiff)
                ->get();

            // добавляем новые пермишены к существующим
            $permissionDB = $permissionDB + $newPerms->pluck('name', 'id')->toArray();

        }

        return array_keys($permissionDB);
    }
}


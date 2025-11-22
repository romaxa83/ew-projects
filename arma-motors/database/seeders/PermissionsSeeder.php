<?php

namespace Database\Seeders;

use App\Models\Admin\Admin;
use App\Models\Permission\GroupPermission;
use App\Models\Permission\GroupPermissionTranslation;
use App\Models\Permission\Permission;
use App\Models\Permission\PermissionTranslation;

class PermissionsSeeder extends BaseSeeder
{
    public function run(): void
    {
//        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
//        \DB::table('permissions')->truncate();
//        \DB::table('permission_translations')->truncate();
//        \DB::table('permission_groups')->truncate();
//        \DB::table('permission_group_translations')->truncate();
//        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $permissions = array_map('str_getcsv', file(__DIR__.'/_permissions.csv'));

        try {
            \DB::transaction(function () use ($permissions) {

                collect($permissions)->each(function($perm){
                    $group = current(explode('.', $perm[0]));

                    $groupModel = null;
                    if($m = GroupPermission::where('name', $group)->first()){
                        $groupModel = $m;
                    } else {
                        $groupModel = new GroupPermission();
                        $groupModel->name = $group;
                        $groupModel->sort = $perm[5];
                        $groupModel->save();

                        $gt_ru = new GroupPermissionTranslation();
                        $gt_ru->lang = 'ru';
                        $gt_ru->name = $perm[3];
                        $gt_ru->group_id = $groupModel->id;
                        $gt_ru->save();

                        $gt_uk = new GroupPermissionTranslation();
                        $gt_uk->lang = 'uk';
                        $gt_uk->name = $perm[4];
                        $gt_uk->group_id = $groupModel->id;
                        $gt_uk->save();
                    }

                    if(!Permission::where('name',  $perm[0])->exists()){
                        $p = new Permission();
                        $p->name = $perm[0];
                        $p->guard_name = Admin::GUARD;
                        $p->group_id = $groupModel->id;
                        $p->save();

                        $p_ru = new PermissionTranslation();
                        $p_ru->name = $perm[1];
                        $p_ru->lang = 'ru';
                        $p_ru->permission_id = $p->id;
                        $p_ru->save();

                        $p_uk = new PermissionTranslation();
                        $p_uk->name = $perm[2];
                        $p_uk->lang = 'uk';
                        $p_uk->permission_id = $p->id;
                        $p_uk->save();
                    }
                });
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }
}

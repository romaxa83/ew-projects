<?php

namespace Database\Seeders;

use App\Models\Admin\Admin;
use App\Models\Permission\Role;
use App\Models\Permission\RoleTranslation;
use App\Models\User\User;

class RolesSeeder extends BaseSeeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('roles')->truncate();
        \DB::table('roles_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        try {
            \DB::transaction(function (){
                foreach ($this->data() as $data){
                    $r = new Role();
                    $r->name = $data['name'];
                    $r->guard_name = $data['guard_name'];
                    $r->save();

                    foreach ($data['translations'] as $locale => $name){
                        $rt = new RoleTranslation();
                        $rt->lang = $locale;
                        $rt->name = $name;
                        $rt->role_id = $r->id;
                        $rt->save();
                    }
                }
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    protected function data(): array
    {
        return [
            [
                'name' => config('permission.roles.super_admin'),
                'guard_name' => Admin::GUARD,
                'translations' => [
                    'ru' => 'Бог системы',
                    'uk' => 'Бог системи'
                ]
            ],
            [
                'name' => 'admin',
                'guard_name' => Admin::GUARD,
                'translations' => [
                    'ru' => 'Первый среди равных',
                    'uk' => 'Перший серед рівних'
                ]
            ],
        ];
    }
}

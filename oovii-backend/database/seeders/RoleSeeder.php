<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Throwable;
use WezomCms\Core\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        if (!$roles = Role::count()) {
            try {
                DB::transaction(function () {
                    $data = $this->getData();

                    foreach ($data as $sort => $item) {
                        $model = new Role();
                        $model->name = $item['name'];
                        $model->permissions = $item['permissions'];

                        $model->save();
                    }
                });
            } catch (Throwable $e) {
                dd($e->getMessage());
            }
        }
    }

    protected function getData(): array
    {
        return [
            [
                'name' => Role::DEFAULT_MODERATOR,
                'permissions' => [],
            ],
            [
                'name' => Role::DEFAULT_PROVIDER,
                'permissions' => [
                    'orders.view',
                    'orders.show',
                    'orders.create',
                    'orders.edit',
                    'orders.delete',
                    'orders.restore',
                    'orders.force-delete',
                    'product-reviews.view',
                    'product-reviews.create',
                    'product-reviews.edit',
                    'product-reviews.delete',
                    'products.view',
                    'products.create',
                    'products.edit',
                    'products.delete',
                    'products.restore',
                    'products.force-delete',
                    'products.copy',
                    'imports.view',
                    'imports.create',
                    'imports.edit',
                    'imports.delete',
                ],
            ],
        ];
    }
}

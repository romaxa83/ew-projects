<?php

namespace App\Console\Commands\Init;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use WezomCms\Catalog\Database\Seeders\ColorSpecificationsSeeder;
use WezomCms\Catalog\Database\Seeders\SpecificationsSeeder;
use WezomCms\Catalog\Models\Labels\LabelTranslation;
use WezomCms\Catalog\Repositories\LabelRepository;
use WezomCms\Core\Models\Role;
use WezomCms\Firebase\Helpers\SetTemplates;

class SetAppData extends Command
{
    protected $signature = 'cmd:set-data';

    protected $description = 'Устанавливает обязательные данные для приложения';

    public function __construct(protected LabelRepository $labelRepo)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->createDefaultRoles();
        $this->seedGenderLabel();

//        $this->removeSpecification();
        app(SpecificationsSeeder::class)->run();
        Artisan::call('cmd:set-template');
    }

    private function removeSpecification()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('specifications')->truncate();
        \DB::table('specification_translations')->truncate();
        \DB::table('spec_values')->truncate();
        \DB::table('spec_value_translations')->truncate();
        \DB::table('product_specifications')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function createDefaultRoles()
    {
        if(!Role::query()->where('name', Role::DEFAULT_PROVIDER)->exists()){
            $p = new Role();
            $p->name = Role::DEFAULT_PROVIDER;
            $p->permissions = ["users.view", "users.create", "users.edit", "users.delete", "users.edit-settings"];
            $p->save();
            $this->info("Create role [{$p->name}]");
        } else {
            $this->warn("Role [Provider] exist");
        }

        if(!Role::query()->where('name', Role::DEFAULT_MODERATOR)->exists()){
            $p = new Role();
            $p->name = Role::DEFAULT_MODERATOR;
            $p->permissions = ["providers.view", "providers.create", "providers.edit", "providers.delete", "providers.edit-settings"];
            $p->save();
            $this->info("Create role [{$p->name}]");
        } else {
            $this->warn("Role [Moderator] exist");
        }
    }

    private function seedGenderLabel()
    {
        $genders = ['Мужчинам', 'Женщинам', 'Детям'];

        foreach ($genders as $item){
            if($this->labelRepo->existByName($item)){
                $this->warn("Label [{$item}] exist");
                continue;
            }

            $model = new \WezomCms\Catalog\Models\Labels\Label();
            $model->is_gender = true;
            $model->save();

            foreach(app('locales') as $slug => $language){
                $t = new LabelTranslation();
                $t->label_id = $model->id;
                $t->locale = $slug;
                $t->name = $item;
                $t->save();
            }
            $this->info("Create label [$item]");
        }
    }
}


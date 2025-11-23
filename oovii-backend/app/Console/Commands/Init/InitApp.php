<?php

namespace App\Console\Commands\Init;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use WezomCms\Catalog\Database\Seeders\ColorSpecificationsSeeder;
use WezomCms\Catalog\Models\Labels\LabelTranslation;
use WezomCms\Catalog\Repositories\LabelRepository;
use WezomCms\Core\Models\Administrator;

class InitApp extends Command
{
    protected $signature = 'cmd:init';


    public function __construct(protected LabelRepository $labelRepo)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->createAdmin();
    }

   private function createAdmin()
   {
       if (Administrator::query()->where('super_admin', true)->exists()) {
           $this->info("Super admin exist");
           return;
       }

       $model = new Administrator();
       $model->name = 'admin';
       $model->email = 'admin@gmail.com';
       $model->password = Hash::make("password");
       $model->active = true;
       $model->super_admin = true;
       $model->notify = true;
       $model->save();

       $this->info("Super admin create");
   }
}

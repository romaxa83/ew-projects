<?php

namespace App\Console\Commands\Init;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use WezomCms\Catalog\Database\Seeders\ColorSpecificationsSeeder;
use WezomCms\Catalog\Database\Seeders\LabelRemoveSeeder;
use WezomCms\Catalog\Database\Seeders\LabelSeeder;
use WezomCms\Catalog\Database\Seeders\ProductRemoveSeeder;
use WezomCms\Catalog\Database\Seeders\ProductSeeder;
use WezomCms\Catalog\Database\Seeders\SpecificationsSeeder;
use WezomCms\Providers\Database\Seeders;

class Seeder extends Command
{
    protected $signature = 'cmd:seed {--r}';

    protected $description = 'Run seeder (-r - remove old rows)';

    protected array $seeders = [
//        'provider' => Seeders\ProviderSeeder::class,
//        'label' => LabelSeeder::class,
//        'product' => ProductSeeder::class,
        'specification' => SpecificationsSeeder::class,
    ];

    protected array $seedersRemove = [
        'provider' => Seeders\ProviderRemoveSeeder::class,
        'label' => LabelRemoveSeeder::class,
        'product' => ProductRemoveSeeder::class,
//        'specification' => ColorSpecificationsSeeder::class,
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if($this->option('r')){
            $this->seedBeforeRemove();
            $this->info("Done");
            return;
        }

        $this->seed();
        $this->info("Done");
    }

    public function seed()
    {
        foreach ($this->seeders as $name => $seeder){
            Artisan::call('db:seed', ['--class' => $seeder]);
            $this->info("Run {$name} seeder");
        }
//
//
//
//        Artisan::call('db:seed', ['--class' => LabelSeeder::class,]);
//        $this->info("Run label seeder");
//
//        Artisan::call('db:seed', ['--class' => ProductSeeder::class,]);
//        $this->info("Run product seeder");
    }

    public function seedBeforeRemove()
    {
        foreach ($this->seedersRemove as $name => $seeder){
            Artisan::call('db:seed', ['--class' => $seeder]);
            $this->info("Run {$name} seeder (remove old)");
        }
    }
}

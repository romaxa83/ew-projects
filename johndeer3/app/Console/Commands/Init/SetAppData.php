<?php

namespace App\Console\Commands\Init;

use App\Console\Commands\Init\SetData\JdData;
use App\Console\Commands\Init\SetData\Locale;
use App\Console\Commands\Init\SetData\Roles;
use App\Console\Commands\Notification\SetNotificationTemplate;
use Database\Seeders\NationalitiesSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetAppData extends Command
{
    protected $signature = 'cmd:set-data';

    protected $description = 'Загрузка данных для приложения';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Set Languages");
        app(Locale::class)->run();

        $this->info("Set Roles with translation");
        app(Roles::class)->run();

        $this->info("Set Nationality");
        resolve(NationalitiesSeeder::class)->run();

        $this->info("Set Notification Template");
        resolve(SetNotificationTemplate::class)->handle();

        if(\App::environment("testing")){

            $this->info("Set JD data");
            app(JdData::class)->run();
        }
    }
}



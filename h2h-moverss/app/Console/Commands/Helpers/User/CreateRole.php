<?php

namespace App\Console\Commands\Helpers\User;

use App\Models\User\Role;
use Illuminate\Console\Command;

class CreateRole extends Command
{
    protected $signature = 'helpers:user_create_role {name?}';

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

            echo PHP_EOL;
            $this->info("Done [time = {$time}]");
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    public function exec()
    {
        $name = $this->argument('name');
        if(is_null($name)){
            $name = $this->ask('Role name');
        }

        if($name) {
            if(Role::where('title', $name)->exists()) {
                $this->warn('Role ['.$name.'] already exists');
               return;
            }

            $model = new Role();
            $model->title = $name;
            $model->save();

            $this->info('Role ['.$name.'] created');
        }
    }
}

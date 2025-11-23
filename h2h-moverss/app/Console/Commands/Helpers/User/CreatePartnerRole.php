<?php

namespace App\Console\Commands\Helpers\User;

use App\Models\User\Role;
use App\Models\User\RouteList;
use Illuminate\Console\Command;

class CreatePartnerRole extends Command
{
    protected $signature = 'helpers:create_partner_role';

    protected array $routes = [
        'orders.record',
        'dispatch.schedule',
        'company.trucks.records'
    ];

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
        if($model = Role::where('title', Role::PARTNER)->first()) {
            $this->warn('Role ['.Role::PARTNER.'] already exists');
        } else {
            $model = new Role();
            $model->title = Role::PARTNER;
            $model->save();

            $this->info('Role ['.Role::PARTNER.'] created');
        }

        if($count = \DB::table('users_roles_2_routes')->where('role_id', $model->id)->delete()){
            $this->warn("remove {$count} routes");
        }


        $routes = RouteList::query()->whereIn('name', $this->routes)->get();
        foreach($routes as $route) {
            \DB::table('users_roles_2_routes')->insert([
                'role_id' => $model->id,
                'route_id' => $route->id,
            ]);
            $this->info("attach route [{$route->name}] to role");
        }
    }
}

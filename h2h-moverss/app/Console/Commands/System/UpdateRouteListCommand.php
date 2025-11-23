<?php

namespace App\Console\Commands\System;

use App\Http\Controllers\System\UpdateRouteListController;
use Illuminate\Console\Command;

class UpdateRouteListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:update-route-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update list of system routes for control access rules';

    /**
     * Execute the console command.
     *
     * @param  UpdateRouteListController  $controller
     * @return int
     */
    public function handle(UpdateRouteListController $controller)
    {
        $controller->sync();
    }
}

<?php

namespace App\Console\Commands\Helpers;

use App\Models\Admin\Admin;
use App\Models\User\Car;
use App\Models\User\User;
use Illuminate\Console\Command;

class RemoveOldGuid extends Command
{
    protected $signature = 'am:remove_old_guid';

    protected $description = '';

    public function handle()
    {
//        $m = Admin::first();
//
//        dd(isset($m->dealership->alias));


        try {
//            $this->users();
//            $this->cars();
        } catch(\Throwable $e){
            $this->error($e->getMessage());
        }
    }

    private function users()
    {
        $res = User::query()
            ->whereNotNull('uuid')
            ->update(['uuid' => null]);

        $this->info("[sync] - update users [{$res}]");
    }

    private function cars()
    {
        $res = Car::query()
            ->whereNotNull('uuid')
            ->update(['uuid' => null]);

        $this->info("[sync] - update cars [{$res}]");
    }
}

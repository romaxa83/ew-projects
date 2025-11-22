<?php

namespace App\Console\Commands\Init;

use App\Models\User\User;
use Illuminate\Console\Command;

class Init extends Command
{
    protected $signature = 'am:init';

    protected $description = 'Init data for app';

    /**
     * @throws \Exception
     */
    public function handle()
    {
//        $this->info('create oauth clients for admin/user');
//        $this->call("passport:client", [ "--password", "--provider=admins", "--name='Admins'"]);
//        $this->call("passport:client", [ "--password", "--provider=users", "--name='Users'"]);
//        $this->call("passport:client --password --provider=admins --name='Admins'");
//        $this->call("passport:client --password --provider=users --name='Users'");

        $this->call('am:create-admin');
    }

}

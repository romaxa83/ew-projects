<?php

namespace App\Console\Commands\Ldap;

use Adldap\Laravel\Facades\Adldap;
use Illuminate\Console\Command;
use Throwable;

class TestLdapConnectionCommand extends Command
{
    protected $signature = 'ldap:test-connection';

    public function handle(): int
    {
        try {
            Adldap::search()->users()->find('coopadmin');

            $this->info('Connection to LDAP is working');
        } catch (Throwable $e) {
            logger($e);

            $this->info('Not working');
        }

        return self::SUCCESS;
    }
}

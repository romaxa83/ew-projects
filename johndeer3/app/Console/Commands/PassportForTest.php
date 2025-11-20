<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Class PassportForTest
 *
 * @package App\Console\Commands
 */
class PassportForTest extends Command
{
    protected $oauthKey = 'KlbYRjMNPQ9mUOO97LGpHXWANOfr065KXbhHSiva';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates authorization data for a test environment';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        if(DB::table('oauth_clients')->count()){
            DB::table('oauth_clients')->truncate();
        }

        DB::table('oauth_clients')->insert([
            [
                'id' => 1,
                'name' => 'Personal Access Client',
                'secret' => '55NDSu9m1psvfXDZJ8qtFmb8C6hIyCN3lOgaK20p',
                'redirect' => 'http://localhost',
                'personal_access_client' => 1,
                'password_client' => 0,
                'revoked' => 0,
                'created_at' => '2019-11-07 15:53:16',
                'updated_at' => '2019-11-07 15:53:16',
            ],
            [
                'id' => 2,
                'name' => 'Password Grant Client',
                'secret' => $this->oauthKey,
                'redirect' => 'http://localhost',
                'personal_access_client' => 0,
                'password_client' => 1,
                'revoked' => 0,
                'created_at' => '2019-11-07 15:53:16',
                'updated_at' => '2019-11-07 15:53:16',
            ],
        ]);

        $this->writeEnv();
        $this->info('Create oauth-key for test');
    }

    private function writeEnv()
    {
        $pathEnv = realpath('.env.testing');

        file_put_contents($pathEnv, preg_replace(
            '/^OAUTH_SECRET_KEY=key/m',
            'OAUTH_SECRET_KEY=' . $this->oauthKey,
            file_get_contents($pathEnv)
        ));
    }

}

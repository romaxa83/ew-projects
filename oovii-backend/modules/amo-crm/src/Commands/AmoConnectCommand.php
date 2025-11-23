<?php

namespace WezomCms\AmoCrm\Commands;

use Exception;
use Illuminate\Console\Command;
use WezomCms\AmoCrm\Services\AmoCrmService;

class AmoConnectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amo-crm:connect {authorization_code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $accessToken = AmoCrmService::createClient()->getOAuthClient()
                ->getAccessTokenByCode($this->argument('authorization_code'));

            AmoCrmService::updateConnectionSettings([
                'sub_domain' => config('cms.amo-crm.amo-crm.sub_domain'),
                'token_type' => array_get($accessToken->getValues(), 'token_type'),
                'access_token' => $accessToken->getToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'expires' => $accessToken->getExpires(),
            ]);

            $this->info('AmoCRM connected.');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}

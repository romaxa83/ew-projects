<?php

namespace App\Console\Commands;

use App\Services\Token\ApiToken;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GetAPIToken extends Command
{
    protected $signature = 'am:get-api-token';

    protected $description = 'Get api token for AA';

    public function __construct(protected ApiToken $apiToken)
    {
        parent::__construct();
    }

    public function handle()
    {
        try {

//            $path = __DIR__ . 'ZIP.zip';
//
//            $zip = new ZipArchive;
//            dd($zip->open($path));
//
//            dd($zip);
//            $zip->setPassword('password');
//            Collection::times($zip->numFiles, function ($i) use ($zip) {
//                $zip->setEncryptionIndex($i - 1, ZipArchive::EM_AES_256);
//            });
//            $zip->close();

            $config = config('aa.access');

            if(!$config['enable']){
                $this->warn('использование токена отключено');

                return ;
            }

            $this->info($this->apiToken->getToken());

        } catch(\Throwable $e){
            $this->error($e->getMessage());
        }
    }
}

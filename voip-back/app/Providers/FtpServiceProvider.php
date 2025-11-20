<?php

namespace App\Providers;

use App\Services\FTP\FTPClient;
use App\Services\FTP\FTPSimpleClient;
use Illuminate\Support\ServiceProvider;

class FtpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerFTPClient();
    }

    protected function registerFTPClient()
    {
        $this->app->singleton(FTPClient::class, function ($app) {
            return new FTPSimpleClient();
        });
    }
}


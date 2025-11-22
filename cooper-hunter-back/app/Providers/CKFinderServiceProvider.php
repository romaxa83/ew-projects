<?php

namespace App\Providers;

use App\CKFinder\CKFinder;
use CKSource\CKFinderBridge\Command\CKFinderDownloadCommand;
use Exception;
use Symfony\Component\HttpKernel\Kernel;

class CKFinderServiceProvider extends \CKSource\CKFinderBridge\CKFinderServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([CKFinderDownloadCommand::class]);
        }

        $this->app->bind('ckfinder.connector', function () {
            if (!class_exists('\CKSource\CKFinder\CKFinder')) {
                throw new Exception(
                    "Couldn't find CKFinder conector code. " .
                    "Please run `artisan ckfinder:download` command first."
                );
            }

            $ckfinderConfig = config('ckfinder');

            if (is_null($ckfinderConfig)) {
                throw new Exception(
                    "Couldn't load CKFinder configuration file. " .
                    "Please run `artisan vendor:publish --tag=ckfinder` command first."
                );
            }

            $ckfinder = new CKFinder($ckfinderConfig);

            if (Kernel::MAJOR_VERSION === 4) {
                $this->setupForV4Kernel($ckfinder);
            }

            return $ckfinder;
        });
    }
}

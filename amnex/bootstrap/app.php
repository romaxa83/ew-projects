<?php

use App\Console\Commands\Workers\TransferQuoteToExpire;
use App\Jobs\TransferQuoteToExpireJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Wezom\Core\ExtendPackage\PackageManifest;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withCommands([
        __DIR__ . '/../app/Console/Commands',
    ])
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(TransferQuoteToExpireJob::class)
            ->everyThirtyMinutes();
    })
    ->create();

// Extend PackageManifest class
$app->extend(Illuminate\Foundation\PackageManifest::class, function ($instance, $app) {
    return new PackageManifest(
        new Filesystem(),
        $app->basePath(),
        $app->getCachedPackagesPath()
    );
});

return $app;

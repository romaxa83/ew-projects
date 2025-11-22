<?php


namespace App\Providers;


use App\Http\Controllers\Api\Logs\LogsController;
use App\Listeners\HistoryEventSub;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Route;

class HistoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (config('history.enabled')) {
            Event::subscribe(HistoryEventSub::class);
        }

        Route::middleware('auth:api')->group(
            fn() => Route::get('api/logs', [LogsController::class, 'index'])->name('logs')
        );
    }
}

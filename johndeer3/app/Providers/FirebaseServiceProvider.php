<?php

namespace App\Providers;

use App\Services\FcmNotification\Sender\FirebaseSender;
use App\Services\FcmNotification\Sender\SimpleFirebaseSender;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FirebaseSender::class, function (Application $app) {
            $config = $app->make('config')->get('firebase');

            return new SimpleFirebaseSender(
                $config['fcm_send_url'],
                $config['firebase_server_key']
            );
        });
    }
}

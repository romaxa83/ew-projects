<?php

return [
    'client_timezone_default' => env('CLIENT_TIMEZONE_DEFAULT', 'America/Chicago'),
    'name' => env('APP_NAME', 'Laravel'),
    'info_email' => env('HAUL_INFO_EMAIL', 'info@haulk.app'),
    'sync_bs_token' => env('SYNC_BS_TOKEN'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL', null),
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'email' => [
        'sending_in_time' => [
            // minutes
            'not_confirm_signup' => env('EMAIL_SENDING_IF_NOT_CONFIRM_SIGNUP', 60 * 24 * 3),  // 3 days
            'not_confirm_second_remind' => env('EMAIL_NOT_CONFIRM_SECOND_REMIND', 60 * 24 * 2), // 2 days
            'not_confirm_third_remind' => env('EMAIL_NOT_CONFIRM_THIRD_REMIND', 60 * 24 * 3), // 3 days
            'not_confirm_final_remind' => env('EMAIL_NOT_CONFIRM_FINAL_REMIND', 60 * 24 * 5), // 5 days

            'not_payment_cart_first_remind' => env('EMAIL_NOT_PAYMENT_CARD_FIRST_REMIND', 60 * 24 * 3), // 3 days
            'not_payment_cart_second_remind' => env('EMAIL_NOT_PAYMENT_CARD_SECOND_REMIND', 60 * 24 * 5), // 5 days
            'not_payment_cart_final_remind' => env('EMAIL_NOT_PAYMENT_CARD_FINAL_REMIND', 60 * 24 * 7), // 7 days

            'not_login_free_trial_first_remind' => env('EMAIL_NOT_LOGIN_FREE_TRIAL_FIRST_REMIND', 60 * 24 * 3), // 3 days
            'not_login_free_trial_second_remind' => env('EMAIL_NOT_LOGIN_FREE_TRIAL_SECOND_REMIND', 60 * 24 * 7), // 7 days
            'not_login_free_trial_final_remind' => env('EMAIL_NOT_LOGIN_FREE_TRIAL_FINAL_REMIND', 60 * 24 * 14), // 14 days

            'before_trial_end' => env('EMAIL_BEFORE_TRIAL_END', 60 * 24), // 1 day

            'not_paid_first_remind' => env('EMAIL_NOT_PAID_FIRST_REMIND', 60 * 24 * 7), // 7 day
            'not_paid_second_remind' => env('EMAIL_NOT_PAID_SECOND_REMIND', 60 * 24 * 14), // 14 day
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        L5Swagger\L5SwaggerServiceProvider::class,
        EloquentFilter\ServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\HistoryServiceProvider::class,
        App\Providers\FlespiServiceProvider::class,
        App\Providers\NotificationServiceProvider::class,
        App\Providers\GoogleServiceProvider::class,
        App\Providers\BodyShopServiceProvider::class,
        App\Providers\SendPulseServiceProvider::class,
        App\Providers\TelegramServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
    ],
];

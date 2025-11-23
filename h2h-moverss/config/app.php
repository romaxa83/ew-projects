<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool)env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => env('TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

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

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\MenuServiceProvider::class,

        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

        Barryvdh\Debugbar\ServiceProvider::class,
        Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class,

        Yajra\DataTables\DataTablesServiceProvider::class,
        Yajra\DataTables\HtmlServiceProvider::class,
        Yajra\DataTables\EditorServiceProvider::class,
        Yajra\DataTables\ButtonsServiceProvider::class,

        Dacastro4\LaravelGmail\LaravelGmailServiceProvider::class,
        OwenIt\Auditing\AuditingServiceProvider::class,
        Maatwebsite\Excel\ExcelServiceProvider::class,
        App\Providers\TelegramServiceProvider::class,
        App\Providers\RequestServiceProvider::class,
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
        'Http' => Illuminate\Support\Facades\Http::class,
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
        'Debugbar' => Barryvdh\Debugbar\Facade::class,
        'Datatables' => Yajra\Datatables\Facades\Datatables::class,
        'Twilio' => Aloha\Twilio\Support\Laravel\Facade::class,
        'Excel' => Maatwebsite\Excel\Facades\Excel::class,
    ],

    /**
     * Services
     */
    'tg' => [
        'errors_hash' => env('TELEGRAM_BOT_ERRORS_HASH'),
        'errors_chat' => env('TELEGRAM_BOT_ERRORS_CHAT'),
        'bot_documents_hash' => env('TELEGRAM_BOT_DOCUMENTS_HASH'),
        'bot_documents_chat' => env('TELEGRAM_BOT_DOCUMENTS_CHAT'),
    ],

    'google' => [
        'maps' => [
            'key' => env('G_MAPS_KEY'),
        ]
    ],

    'mail_jet' => [
        'public' => env('MJ_APIKEY_PUBLIC'),
        'private' => env('MJ_APIKEY_PRIVATE'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'divisions' => [
            'la' => [
                'sms_caller_id' => env('TWILIO_LA_SMS_CALLER_ID'),
            ],
            'il' => [
                'sms_caller_id' => env('TWILIO_IL_SMS_CALLER_ID'),
            ],
        ]
    ],

    'ring_central' => [
        'is_prod' => (bool)env('RING_CENTRAL_IS_PROD', false),
        'client' => env('RING_CENTRAL_CLIENT'),
        'token' => env('RING_CENTRAL_TOKEN'),
        'account_id' => env('RING_CENTRAL_ACCOUNT_ID'),
        'login' => env('RING_CENTRAL_LOGIN'),
        'password' => env('RING_CENTRAL_PWD'),
    ],

    /**
     * App Variables
     */
    'formatter_currency_locale' => 'en_US',
    'formatter_currency' => 'USD',

    'site_import' => [
        'la' => [
            'mf_public' => env('IMPORT_la_SITE_MF_PUBLIC'),
            'mf_private' => env('IMPORT_la_SITE_MF_PRIVATE'),
            'site_url' => env('IMPORT_la_SITE_MF_URL'),
            'division_id' => (int)env('IMPORT_la_SITE_MF_BRANCH_ID'),
        ],
        'h2h' => [
            'mf_public' => env('IMPORT_h2h_SITE_MF_PUBLIC'),
            'mf_private' => env('IMPORT_h2h_SITE_MF_PRIVATE'),
            'site_url' => env('IMPORT_h2h_SITE_MF_URL'),
            'division_id' => (int)env('IMPORT_h2h_SITE_MF_BRANCH_ID'),
        ]
    ],

    'moving_types' => [
        'local' => [
            'id' => 1,
            'title' => 'Local'
        ],
        'intrastate' => [
            'id' => 2,
            'title' => 'Intrastate'
        ],
        'interstate' => [
            'id' => 3,
            'title' => 'Interstate'
        ],
    ],

    'phone_types' => [
        1 => 'Home',
//        2 => 'Work',
        3 => 'Mobile',
//        4 => 'Main',
//        5 => 'Fax',
//        6 => 'Other',
        7 => 'Relations',
        8 => 'Neighbors',
    ],


//    'waypoints_flights' => [
//        1 => [
//            'title' => '1 flight'
//        ],
//        2 => [
//            'title' => '2 flights'
//        ],
//        3 => [
//            'title' => '3 flights'
//        ],
//        4 => [
//            'title' => '4 flights'
//        ],
//        5 => [
//            'title' => '5+ flights'
//        ],
//    ],

    'calculated_table' => [
        'moving' => ['description' => 'Moving services', 'sort' => 1], // local and intrastate

        'labor' => ['description' => 'Moving services', 'sort' => 1], // interstate
        'fuel' => ['description' => 'Fuel surcharge', 'sort' => 2], // interstate
        'elevators' => ['description' => 'Elevator surcharge', 'sort' => 3], // interstate
        'floors' => ['description' => 'Stairs surcharge', 'sort' => 4], // interstate
        'packing' => ['description' => 'Packing services', 'sort' => 5], // interstate
        'unpacking' => ['description' => 'Unpacking services', 'sort' => 6], // interstate
        'shuttle' => ['description' => 'Shuttle services', 'sort' => 7],  // interstate

        'fee' => ['description' => 'Travel Fee', 'sort' => 20],
        'subtotal' => ['description' => 'Subtotal', 'sort' => 25], // only local

        'materials' => ['description' => 'Extra', 'sort' => 30],
        'discount' => ['description' => 'Discount', 'sort' => 50],
        'total' => ['description' => 'Total', 'sort' => 90],
        'paid' => ['description' => 'Paid', 'sort' => 92],
        'left2pay' => ['description' => 'Balance due', 'sort' => 95],
        'overpaid' => ['description' => 'Overpaid', 'sort' => 100],
    ]

];

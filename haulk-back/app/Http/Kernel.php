<?php

namespace App\Http;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\BsAuth;
use App\Http\Middleware\CheckForMaintenanceMode;
use App\Http\Middleware\ConfigLanguages;
use App\Http\Middleware\DebugApi;
use App\Http\Middleware\DraftStore;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\FileBrowserAuthorization;
use App\Http\Middleware\FlespiAuth;
use App\Http\Middleware\Language;
use App\Http\Middleware\MobileDataToJson;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RevokeTokenIfUserInactive;
use App\Http\Middleware\SwaggerFix;
use App\Http\Middleware\ThrottleRequestsWithIp;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\WebCrmPanel;
use Fruitcake\Cors\HandleCors;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\Permission\Middlewares\PermissionMiddleware;

class Kernel extends HttpKernel
{

    protected $middleware = [
        ConfigLanguages::class,
        TrustProxies::class,
        CheckForMaintenanceMode::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        SwaggerFix::class,
        HandleCors::class,
        Language::class,
        WebCrmPanel::class,
        DebugApi::class,
        RevokeTokenIfUserInactive::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
        ],

        'api' => [
            'throttleIp:6000,1',
            SubstituteBindings::class,
            'draft',
        ],
    ];

    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'filebrowser_auth' => FileBrowserAuthorization::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'bindings' => SubstituteBindings::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'permission' => PermissionMiddleware::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePassword::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
        'raw' => MobileDataToJson::class,
        'draft' => DraftStore::class,
        'throttleIp' => ThrottleRequestsWithIp::class,
        'flespi.auth' => FlespiAuth::class,
        'bs.auth' => BsAuth::class,
    ];

    protected $middlewarePriority = [
        ThrottleRequests::class,
        FileBrowserAuthorization::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        Authenticate::class,
        AuthenticateSession::class,
        SubstituteBindings::class,
        Authorize::class,
        RevokeTokenIfUserInactive::class,
        Language::class,
    ];
}

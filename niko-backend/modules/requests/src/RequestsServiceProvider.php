<?php

namespace WezomCms\Requests;

use WezomCms\Core\BaseServiceProvider;
use WezomCms\Requests\Events\OrderRequest;
use WezomCms\Requests\Events\VerifyCarRequest;
use WezomCms\Requests\Listeners\OrderRequestListener;
use WezomCms\Requests\Listeners\VerifyCarRequestListener;

class RequestsServiceProvider extends BaseServiceProvider
{
    protected $listen = [
        VerifyCarRequest::class => [
            VerifyCarRequestListener::class,
        ],
        OrderRequest::class => [
            OrderRequestListener::class,
        ],
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(){

//        if(env('1C_USE', false)){
//            $this->app->singleton(Client::class, function (Application $app) {
//
//                return new Client([
//                    'base_uri' => env('1C_BASE_URL')
//                ]);
//            });
//        }


    }


    /**
     * Application booting.
     */
    public function boot()
    {

//        if(env('USE_SMS_SENDER', false)){
//            if(env('SMS_DRIVER') == 'turbosms'){
//
//                $this->app->singleton(SmsSender::class, function (Application $app) {
//                    return new TurboSmsSender();
//                });
//            }
//        }

        parent::boot();
    }
}


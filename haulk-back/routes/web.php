<?php

use App\Http\Controllers\Controller;

Route::get('/', [Controller::class, 'root']);

//Route::get(
//    '/mail/email',
//    function () {
//        return app(\Illuminate\Mail\Markdown::class)
//            ->render(
//                'notifications::email',
//                [
//                    'level' => 'success',
//                    'greeting' => 'Hello Друже бобер',
//                    'introLines' => [
//                        'Hey, click the button to download driver app.',
//                    ],
//                    'actionText' => 'Download driver app',
//                    'actionUrl' => 'http://192.168.0.1/',
//                    'outroLines' => [
//                        'Thank you for using our service!'
//                    ],
//                    'android' => [
//                        'name' => 'Android',
//                        'image_url' => config('frontend.images.email.google_play'),
//                        'app_url' => config('urls.android_app'),
//                    ],
//                    'ios' => [
//                        'name' => 'Ios',
//                        'image_url' => config('frontend.images.email.app_store'),
//                        'app_url' => config('urls.ios_app'),
//                    ]
//                ]
//            );
//    }
//);

//Route::get(
//    '/mail/change-password',
//    function () {
//          //не полное заполнение полей
//        return app(\Illuminate\Mail\Markdown::class)
//            ->render(
//                'notifications::email',
//                [
//                    'level' => 'success',
//                    'greeting' => 'Hello Друже бобер',
//                    'introLines' => [
//                        "You have made a request to recover your password on the site [Haulk](" . config(
//                            'frontend.url'
//                        ) . ")."
//                        ,
//                    ],
//                    'actionText' => 'Download driver app',
//                    'actionUrl' => 'http://192.168.0.1/',
//                    'outroLines' => [
//                        'Thank you for using our service!'
//                    ],
//                    'android' => [
//                        'name' => 'Android',
//                        'image_url' => config('frontend.images.email.google_play'),
//                        'app_url' => config('urls.android_app'),
//                    ],
//                    'ios' => [
//                        'name' => 'Ios',
//                        'image_url' => config('frontend.images.email.app_store'),
//                        'app_url' => config('urls.ios_app'),
//                    ]
//                ]
//            );
//    }
//);

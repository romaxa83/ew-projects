<?php

use App\Http\Controllers\V2\Carrier\Users\UserController;
use App\Http\Middleware\CheckSubscription;

Route::middleware(
    [
        'auth:api',
        CheckSubscription::class
    ]
)
    ->group(
        function () {
            //profile
            Route::put('profile', 'Users\ProfileController@updateV2')->name('profile.update');

            //Users
            Route::namespace('Users')
                ->group(
                    function () {

                        Route::post('users', [UserController::class, 'storeV2'])
                            ->name('users.store');

                        Route::post('users/{user}', [UserController::class, 'updateV2'])
                            ->name('users.update');
                    }
                );
        }
    );

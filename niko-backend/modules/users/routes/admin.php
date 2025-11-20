<?php

Route::namespace('WezomCms\\Users\\Http\\Controllers\\Admin')
    ->group(function () {
        Route::adminResource('users', 'UsersController')->settings();
        Route::adminResource('user-cars', 'CarController')->settings();
        Route::adminResource('loyalties', 'LoyaltyLevelController');
        Route::get('users/{id}/auth', 'UsersController@auth')->name('users.auth');

        Route::get('users/search', 'UsersController@search')->name('users.search');
    });

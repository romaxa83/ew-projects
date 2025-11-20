<?php

Route::middleware('set.locale')->group(function () {

    Route::namespace('WezomCms\\Services\\Http\\Controllers\\Api\\V1')
        ->group(function () {

            Route::get('catalogs/services', 'ServiceController@listSto')->name('api.services.sto');
            Route::get('catalogs/insurances', 'ServiceController@listInsurance')->name('api.services.insurances');
        });
});



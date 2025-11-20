<?php

Route::middleware('set.locale')->group(function () {

    Route::namespace('WezomCms\\Cars\\Http\\Controllers\\Api\\V1')
        ->group(function () {

            // точка входа для 1с
            Route::middleware('niko.auth')->group(function(){
                Route::get('transmission/1c/list', 'TransmissionController@listFor1C')->name('api.transmission.list-1c');
                Route::get('engine/1c/list', 'EngineTypeController@listFor1C')->name('api.engine-type.list-1c');

                Route::get('actual/models', 'ModelController@actualModel')->name('api.actual.models.list-1c');

                Route::post('brand/1c/sync', 'BrandController@sync')->name('api.brand.sync');
                Route::post('model/1c/sync', 'ModelController@sync')->name('api.model.sync');
            });

            Route::get('catalogs/cars/transmissions', 'TransmissionController@list')->name('api.transmission-list');
            Route::get('catalogs/cars/brands', 'BrandController@list')->name('api.brand-list');
            Route::get('catalogs/cars/brands/{brandId}/models', 'ModelController@forBrand')->name('api.models-for-brand');
            Route::get('catalogs/cars/engines', 'EngineTypeController@list')->name('api.engine-type-list');
        });
});



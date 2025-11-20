<?php

Route::namespace('WezomCms\\Translates\\Http\\Controllers\\Api\\V1')
    ->group(function () {
        Route::get('translates', 'TranslatesController@getTranslates')->name('api.get-translates');
        Route::post('translates', 'TranslatesController@setTranslates')->name('api.set-translates');
        Route::get('translates-hash', 'TranslatesController@getHash')->name('api.hash-translates');

        Route::delete('translates', 'TranslatesController@removeAllTranslate')->name('api.delete-all-translates');
        Route::delete('translates/{key}', 'TranslatesController@removeTranslate')->name('api.delete-translates');
    });

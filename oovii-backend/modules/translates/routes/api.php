<?php

use WezomCms\Translates\Http\Controllers\Api\V1\TranslatesController;

Route::get('mobile/translates', [TranslatesController::class, 'getTranslates'])
    ->name('api.v1.mobile.translates.get');

Route::get('mobile/translates/hash', [TranslatesController::class, 'getHash'])
    ->name('api.v1.mobile.translates.hash');

Route::post('mobile/translates', [TranslatesController::class, 'setTranslates'])
    ->name('api.v1.mobile.translates.set');

Route::delete('mobile/translates/{alias?}', [TranslatesController::class, 'delete'])
    ->name('api.v1.mobile.translates.delete');

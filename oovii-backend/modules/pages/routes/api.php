<?php

use WezomCms\Pages\Http\Controllers\Api\V1\PageController;

Route::middleware('set.locale')->group(function(){
    Route::get('mobile/pages', [PageController::class, 'list'])
        ->name('api.v1.mobile.pages.list');
});

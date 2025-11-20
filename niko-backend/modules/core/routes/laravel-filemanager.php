<?php

use UniSharp\LaravelFilemanager\Lfm;
use WezomCms\Core\Http\Middleware\SetAdminLocale;

Route::prefix('filemanager')
    ->middleware(['web', 'auth:admin', SetAdminLocale::class])
    ->group(function () {
        Lfm::routes();
    });

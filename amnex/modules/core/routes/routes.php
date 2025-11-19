<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::any('/ckfinder/connector', 'Wezom\Core\Http\Controllers\CKFinderController@requestAction')
    ->name('ckfinder_connectors');

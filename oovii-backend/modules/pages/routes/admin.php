<?php

use WezomCms\Pages\Http\Controllers\Admin\PagesController;

Route::adminResource('pages', PagesController::class)->settings();

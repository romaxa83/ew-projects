<?php

Route::namespace('WezomCms\\Services\\Http\\Controllers\\Admin')
    ->group(function () {
        Route::adminResource('services', 'ServicesController')->settings();
        Route::adminResource('service-groups', 'ServiceGroupsController');

    });

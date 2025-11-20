<?php

Route::adminResource(
    'services-orders',
    'WezomCms\\ServicesOrders\\Http\\Controllers\\Admin\\ServicesOrdersController'
)->settings();
Route::adminResource(
    'services-orders-rates',
    'WezomCms\\ServicesOrders\\Http\\Controllers\\Admin\\OrderRateController'
);

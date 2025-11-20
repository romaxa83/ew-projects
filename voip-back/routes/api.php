<?php

use App\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'api.auth',
//    'set.locale'
])->group(function(){

    Route::get('employees', [V1\Employees\EmployeeController::class, 'list'])
        ->name('api.v1.employees');
    Route::post('employees/{id}', [V1\Employees\EmployeeController::class, 'edit'])
        ->name('api.v1.employees.edit');

    Route::get('calls/queues', [V1\Calls\QueueController::class, 'list'])
        ->name('api.v1.calls.queues');
});

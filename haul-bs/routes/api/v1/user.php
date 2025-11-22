<?php

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::post('login', [Api\V1\Users\UserAuthController::class, 'login'])
    ->name('.login');
Route::post('refresh-token', [Api\V1\Users\UserAuthController::class, 'refreshToken'])
    ->name('.refresh-token');
Route::post('password-forgot', [Api\V1\Users\UserAuthController::class, 'forgotPassword'])
    ->name('.forgot-password');
Route::post('reset-password', [Api\V1\Users\UserAuthController::class,'resetPassword'])
    ->name('.reset-password');
Route::post('check-password-token', [Api\V1\Users\UserAuthController::class,'checkPasswordToken'])
    ->name('.check-password-token');

Route::middleware([
    'auth:'.Role::GUARD_USER,
])->group(function () {
    Route::post('logout', [Api\V1\Users\UserAuthController::class, 'logout'])
        ->name('.logout');

    //profile
    Route::get('profile', [Api\V1\Users\UserProfileController::class, 'profile'])
        ->name('.profile');
    Route::put('profile', [Api\V1\Users\UserProfileController::class, 'update'])
        ->name('.profile.update');
    Route::post('profile/upload-photo', [Api\V1\Users\UserProfileController::class, 'uploadPhoto'])
        ->name('.profile.upload-photo');
    Route::delete('profile/delete-photo', [Api\V1\Users\UserProfileController::class, 'deletePhoto'])
        ->name('.profile.delete-photo');

    // users crud
    Route::get('users', [Api\V1\Users\UserCrudController::class, 'index'])
        ->name('');
    Route::get('users/shortlist', [Api\V1\Users\UserCrudController::class, 'shortlist'])
        ->name('.shortlist');
    Route::get('users/{id}', [Api\V1\Users\UserCrudController::class, 'show'])
        ->name('.show');
    Route::post('users', [Api\V1\Users\UserCrudController::class, 'store'])
        ->name('.store');
    Route::put('users/{id}', [Api\V1\Users\UserCrudController::class, 'update'])
        ->name('.update');
    Route::delete('users/{id}', [Api\V1\Users\UserCrudController::class, 'delete'])
        ->name('.delete');
    // users action
    Route::put('users/resend-invitation-link/{id}', [Api\V1\Users\UserActionController::class, 'resendInvitationLink'])
        ->name('.resend-invitation-link');
    Route::put('users/{id}/change-status', [Api\V1\Users\UserActionController::class, 'changeStatus'])
        ->name('.change-status');
    Route::put('users/{id}/change-password', [Api\V1\Users\UserActionController::class, 'changePassword'])
        ->name('.change-password');

    // roles
    Route::get('roles', [Api\V1\Users\RoleController::class, 'list'])
        ->name('.roles');
});

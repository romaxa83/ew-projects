<?php

use WezomCms\Core\Http\Controllers\Admin\AdministratorsController;
use WezomCms\Core\Http\Controllers\Admin\AjaxController;
use WezomCms\Core\Http\Controllers\Admin\DashboardController;
use WezomCms\Core\Http\Controllers\Admin\ImageMultiUploaderController;
use WezomCms\Core\Http\Controllers\Admin\NotFoundController;
use WezomCms\Core\Http\Controllers\Admin\ProfileController;
use WezomCms\Core\Http\Controllers\Admin\RolesController;
use WezomCms\Core\Http\Controllers\Admin\SettingsController;
use WezomCms\Core\Http\Controllers\Admin\TranslationsController;

// Dashboard
Route::get('', [DashboardController::class, 'index']);
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Edit profile
Route::get('profile', [ProfileController::class, 'edit'])->name('edit-profile');
Route::post('profile', [ProfileController::class, 'update'])->name('update-profile');
Route::get('profile/{locale}', [ProfileController::class, 'changeLocale'])->name('change-locale');

// Administrators
Route::adminResource('administrators', AdministratorsController::class);

// Roles
Route::adminResource('roles', RolesController::class);

// Ajax
Route::post('ajax/generate-slug', [AjaxController::class, 'generateSlug'])->name('ajax.generate-slug');
Route::post('ajax/change-status', [AjaxController::class, 'changeStatus'])->name('ajax.change-status');
Route::post('ajax/update-sort', [AjaxController::class, 'updateSort'])->name('ajax.update-sort');
Route::post('ajax/update-nestable-sort', [AjaxController::class, 'updateNestableSort'])
    ->name('ajax.update-nestable-sort');

// Notifications
Route::post('mark-notifications-as-read', [AjaxController::class, 'markNotificationsAsRead'])
    ->name('mark-notifications-as-read');
Route::post('mark-notification-as-read/{id}', [AjaxController::class, 'markNotificationAsRead'])
    ->name('mark-notification-as-read');

// Image multi uploader
Route::prefix('image-multi-uploader')
    ->name('image-multi-uploader.')
    ->group(function () {
        Route::post('save', [ImageMultiUploaderController::class, 'save'])->name('save');
        Route::post('get-uploaded-images', [ImageMultiUploaderController::class, 'getUploadedImages'])
            ->name('get-uploaded-images');
        Route::post('delete', [ImageMultiUploaderController::class, 'delete'])->name('delete');
        Route::post('set-as-default', [ImageMultiUploaderController::class, 'setAsDefault'])->name('set-as-default');
        Route::post('sort', [ImageMultiUploaderController::class, 'sort'])->name('sort');
        Route::get('{id}/rename-form', [ImageMultiUploaderController::class, 'renameForm'])->name('rename-form');
        Route::post('{id}/rename', [ImageMultiUploaderController::class, 'rename'])->name('rename');
    });

// Translations
Route::get('translations/{side}', [TranslationsController::class, 'index'])->name('translations');
Route::post('translations/{side}', [TranslationsController::class, 'update'])->name('translations.update');

// Global settings
Route::settings('settings', SettingsController::class);

Route::get("{fallbackPlaceholder}", NotFoundController::class)
    ->name('fallback')
    ->where('fallbackPlaceholder', '.*')
    ->fallback();

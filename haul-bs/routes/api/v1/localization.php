<?php

use App\Http\Controllers\Api;

Route::get('languages', [Api\V1\Localizations\LanguageController::class, 'list'])
    ->name('.languages');
Route::get('translations', [Api\V1\Localizations\TranslationController::class, 'list'])
    ->name('.translations');
Route::post('translations', [Api\V1\Localizations\TranslationController::class, 'create'])
    ->name('.translations.create');

<?php

use WezomCms\Catalog\Http\Controllers\Admin\BrandsController;
use WezomCms\Catalog\Http\Controllers\Admin\CatalogSeoTemplatesController;
use WezomCms\Catalog\Http\Controllers\Admin\CategoriesController;
use WezomCms\Catalog\Http\Controllers\Admin\CollectionCategoryController;
use WezomCms\Catalog\Http\Controllers\Admin\CollectionController;
use WezomCms\Catalog\Http\Controllers\Admin\LabelController;
use WezomCms\Catalog\Http\Controllers\Admin\ModelsController;
use WezomCms\Catalog\Http\Controllers\Admin\ProductController;
use WezomCms\Catalog\Http\Controllers\Admin\SearchController;
use WezomCms\Catalog\Http\Controllers\Admin\SpecificationsController;
use WezomCms\Catalog\Http\Controllers\Admin\SpecValuesController;

// Categories
Route::adminResource('categories', CategoriesController::class)->settings();

// Collection
Route::adminResource('collection-categories', CollectionCategoryController::class);
Route::adminResource('collections', CollectionController::class)->settings();
Route::post('collections/add-product', [CollectionController::class, 'addProduct'])
    ->name('collection-add-product');
Route::post('collections/delete-product', [CollectionController::class, 'deleteProduct'])
    ->name('collection-delete-product');
// Label
Route::adminResource('labels', LabelController::class)->settings();
// Products
Route::adminResource('products', ProductController::class)->softDeletes()->settings();
// Import
Route::adminResource('imports', ProductController::class)->settings();

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/search', [ProductController::class, 'search'])->name('search');
    Route::post('/{id}/set-sort', [ProductController::class, 'setSort'])->name('set-sort');
    Route::post('/change-category-popup', [ProductController::class, 'changeCategoryPopup'])
        ->name('change-category-popup');
    Route::post('/change-category', [ProductController::class, 'changeCategory'])->name('change-category');
    Route::get('/copy/{id}', [ProductController::class, 'copy'])->name('copy');
    Route::get('/export', [ProductController::class, 'export'])->name('export');
    Route::post('/import', [ProductController::class, 'import'])->name('import');
});

if (config('cms.catalog.brands.enabled')) {
    // Brands
    Route::adminResource('brands', BrandsController::class)->settings();

    Route::prefix('brands')->name('brands.')->group(function () {
        Route::get('/search', [BrandsController::class, 'search'])->name('search');
        Route::get('/get-all', [BrandsController::class, 'getAll'])->name('get-all');
        Route::post('/{id}/set-sort', [BrandsController::class, 'setSort'])->name('set-sort');
    });
}

if (config('cms.catalog.models.enabled')) {
    // Models
    Route::adminResource('models', ModelsController::class)->settings();
    Route::get('models/search', [ModelsController::class, 'search'])->name('models.search');
}

// SEO templates
Route::adminResource('catalog-seo-templates', CatalogSeoTemplatesController::class)->settings();

// Specifications
Route::adminResource('specifications', SpecificationsController::class)->settings();

Route::prefix('spec-values')->name('spec-values.')->group(function () {
    Route::get('/{specification_id}', [SpecValuesController::class, 'list'])->name('list');
    Route::post('/{specification_id}/update-sort', [SpecValuesController::class, 'updateSort'])->name('update-sort');
    Route::post('/{specification_id}/create', [SpecValuesController::class, 'create'])->name('create');
    Route::post('/{specification_id}/save', [SpecValuesController::class, 'save'])->name('save');
    Route::post('/{id}/delete', [SpecValuesController::class, 'delete'])->name('delete');
});

Route::settings('search', SearchController::class);

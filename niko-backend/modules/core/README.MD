## Installation

#### Use console
```
composer require wezom-cms/core
```
#### Or edit composer.json
```
"require": {
    ...
    "wezom-cms/core": "^7.0"
}
```
#### Install dependencies:
```
composer update
```

### Install CMS
```
php artisan cms:install
```
#### Run migrations
```
php artisan migrate
```
#### Create super admin
```
php artisan make:super-admin
```
## Publish
#### Assets
```
php artisan vendor:publish --provider="WezomCms\Core\CoreServiceProvider" --tag="assets"
```
#### Config
```
php artisan vendor:publish --provider="WezomCms\Core\CoreServiceProvider" --tag="config"
```
#### Views
```
php artisan vendor:publish --provider="WezomCms\Core\CoreServiceProvider" --tag="views"
```
#### Laravel JsValidation public
Used for form validation.
```
php artisan vendor:publish --provider="Proengsoft\JsValidation\JsValidationServiceProvider" --tag="public"
``` 
#### Laravel FileManager
Publish the package’s config and assets
```
php artisan vendor:publish --tag=lfm_config
php artisan vendor:publish --tag=lfm_public
```
#### Create storage symbolic link
```
 php artisan storage:link
```
## Register admin routes
To add routing to the admin panel, you can use the RouteRegistrar facade
```php
\RouteRegistrar::adminRoutes(function() {});
// or
\RouteRegistrar::adminRoutesWithoutAuth(function() {});
```
To add site routes
```php
\RouteRegistrar::siteRoutes(function() {});
```

## Create notifications table
```
php artisan notifications:table
php artisan migrate
```

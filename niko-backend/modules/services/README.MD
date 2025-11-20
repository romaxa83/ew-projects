## Installation

#### Use console
```
composer require wezom-cms/services
```
#### Or edit composer.json
```
"require": {
    ...
    "wezom-cms/services": "^7.0"
}
```
#### Install dependencies:
```
composer update
```
#### Package discover
```
php artisan package:discover
```
#### Run migrations
```
php artisan migrate
```

## Publish
#### Views
```
php artisan vendor:publish --provider="WezomCms\Services\ServicesServiceProvider" --tag="views"
```
#### Config
```
php artisan vendor:publish --provider="WezomCms\Services\ServicesServiceProvider" --tag="config"
```
#### Lang
```
php artisan vendor:publish --provider="WezomCms\Services\ServicesServiceProvider" --tag="lang"
```
#### Migrations
```
php artisan vendor:publish --provider="WezomCms\Services\ServicesServiceProvider" --tag="migrations"
```

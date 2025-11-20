## Installation

#### Use console
```
composer require wezom-cms/services-orders
```
#### Or edit composer.json
```
"require": {
    ...
    "wezom-cms/services-orders": "^7.0"
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
php artisan vendor:publish --provider="WezomCms\ServicesOrders\ServicesOrdersServiceProvider" --tag="views"
```
#### Config
```
php artisan vendor:publish --provider="WezomCms\ServicesOrders\ServicesOrdersServiceProvider" --tag="config"
```
#### Lang
```
php artisan vendor:publish --provider="WezomCms\ServicesOrders\ServicesOrdersServiceProvider" --tag="lang"
```
#### Migrations
```
php artisan vendor:publish --provider="WezomCms\ServicesOrders\ServicesOrdersServiceProvider" --tag="migrations"
```

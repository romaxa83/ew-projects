## Installation

#### Use console
```
composer require wezom-cms/users
```
#### Or edit composer.json
```
"require": {
    ...
    "wezom-cms/users": "^7.0"
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
php artisan vendor:publish --provider="WezomCms\Users\UsersServiceProvider" --tag="views"
```
#### Config
```
php artisan vendor:publish --provider="WezomCms\Users\UsersServiceProvider" --tag="config"
```
#### Lang
```
php artisan vendor:publish --provider="WezomCms\Users\UsersServiceProvider" --tag="lang"
```
#### Migrations
```
php artisan vendor:publish --provider="WezomCms\Users\UsersServiceProvider" --tag="migrations"
```

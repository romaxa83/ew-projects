## Installation

#### Use console
```
composer require wezom-cms/articles
```
#### Or edit composer.json
```
"require": {
    ...
    "wezom-cms/articles": "^7.0"
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
php artisan vendor:publish --provider="WezomCms\Articles\ArticlesServiceProvider" --tag="views"
```
#### Lang
```
php artisan vendor:publish --provider="WezomCms\Articles\ArticlesServiceProvider" --tag="lang"
```
#### Migrations
```
php artisan vendor:publish --provider="WezomCms\Articles\ArticlesServiceProvider" --tag="migrations"
```

## Installation

#### Use console
```bash
composer require wezom-cms/product-reviews
```
#### Or edit composer.json
```json
"require": {
    ...
    "wezom-cms/product-reviews": "^8.1"
}
```
#### Update dependencies:
```bash
composer update
```
#### Run migrations
```bash
php artisan migrate
```

## Usage

```xhtml
<livewire:product-reviews.reviews :product="$product" />
```

```xhtml
<livewire:product-reviews.latest-product-reviews :product="$product" />
```

## Publish

#### Views
```bash
php artisan vendor:publish --provider="WezomCms\ProductReviews\ProductReviewsServiceProvider" --tag="views"
```
#### Migrations
```bash
php artisan vendor:publish --provider="WezomCms\ProductReviews\ProductReviewsServiceProvider" --tag="migrations"
```


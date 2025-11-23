## Installation

#### Use console
```bash
composer require wezom-cms/orders
```
#### Or edit composer.json
```json
"require": {
    ...
    "wezom-cms/orders": "^8.1"
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
> For proper work with `users` module remove `.stub` extension from `user_addresses` migration and run it

## Usage

```xhtml
<livewire:orders.header-cart-button/>
```

```xhtml
<livewire:orders.mobile-cart-button/>
```

```xhtml
<livewire:orders.product-list-button :product="$product"/>
```

```xhtml
<livewire:orders.user-addresses/>
```

## Publish
#### Assets
```bash
php artisan vendor:publish --provider="WezomCms\Orders\OrdersServiceProvider" --tag="assets"
```
#### Views
```bash
php artisan vendor:publish --provider="WezomCms\Orders\OrdersServiceProvider" --tag="views"
```
#### Lang
```bash
php artisan vendor:publish --provider="WezomCms\Orders\OrdersServiceProvider" --tag="lang"
```
#### Migrations
```bash
php artisan vendor:publish --provider="WezomCms\Orders\OrdersServiceProvider" --tag="migrations"
```
#### Seeding
Create deliveries
```bash
php artisan db:seed --class="WezomCms\Orders\Database\Seeds\DeliveriesSeeder"
```

Create payments values
```bash
php artisan db:seed --class="WezomCms\Orders\Database\Seeds\PaymentsSeeder"
```

Create basic order statuses
```bash
php artisan db:seed --class="WezomCms\Orders\Database\Seeds\OrderStatusesSeeder"
```

## Installation guide

- Add provider `\Core\Chat\Providers\ChatServiceProvider` to your `configs/app.php` config file
- Publish assets:

```shell
php artisan vendor:publish --tag=chat-configs
php artisan vendor:publish --tag=chat-migrations
```

OR

```shell
php artisan vendor:publish --provider=Core\\Chat\\Providers\\ChatServiceProvider
```

- Run migrations: `php artisan migrate`


- add chat graphql types to your GraphQL config file:

```php
//config.graphql.types:
return [
    //App type_1,
    //...
    //App type_n,
        
    ...config('chat.graphql.types'),
];
```

## TODO

- Write instructions about usage
- make REST API

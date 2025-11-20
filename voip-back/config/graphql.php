<?php

declare(strict_types=1);

use App\GraphQL\Types\Wrappers\PaginationType;
use Core\Http\Controllers\GraphQLController;
use Rebing\GraphQL\GraphQL;

return [
    'prefix' => 'graphql',

    'admin_prefix' => 'graphql/BackOffice',

    'routes' => '{graphql_schema?}',

    'controllers' => GraphQLController::class.'@query',

    'middleware' => [
        'throttle:api',
        App\Http\Middleware\SetAcceptJson::class,
        App\GraphQL\Middlewares\Localization\SystemLangSetterMiddleware::class,
    ],

    'route_group_attributes' => [],

    'default_schema' => 'default',

    'lazyload_types' => false,

    'schema_cache' => env('GRAPHQL_SCHEMA_CACHE', false),

    'error_formatter' => [GraphQL::class, 'formatError'],

    'errors_handler' => [GraphQL::class, 'handleErrors'],

    'params_key' => 'variables',

    'security' => [
        'query_max_complexity' => null,
        'query_max_depth' => null,
        'disable_introspection' => false,
    ],

    'pagination_type' => PaginationType::class,

    'graphiql' => [
        'prefix' => '/graphiql',
        'controller' => GraphQLController::class.'@graphiql',
        'middleware' => [],
        'view' => 'graphql::graphiql',
        'display' => env('ENABLE_GRAPHIQL', true),
    ],

    'defaultFieldResolver' => null,

    'headers' => [],

    'json_encoding_options' => 0,
];

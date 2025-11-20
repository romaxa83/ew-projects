<?php

declare(strict_types=1);

namespace Core\Http\Controllers;

use Core\GraphQL\GraphQL;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GraphQLController extends \Rebing\GraphQL\GraphQLController
{
    /**
     * @throws BindingResolutionException
     */
    public function query(Request $request, string $schema = null): JsonResponse
    {
        $this->app = app();

        /** @var GraphQL $graphql */
        $graphql = $this->app->make('graphql');

        $graphql->setApp($this->app);
        $graphql->clearObjectFromTypes();

        return parent::query($request, $schema);
    }
}

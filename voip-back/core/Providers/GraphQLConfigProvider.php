<?php

namespace Core\Providers;

use Core\GraphQL\GraphQL;
use Illuminate\Contracts\Container\Container;
use Rebing\GraphQL\GraphQLServiceProvider;

class GraphQLConfigProvider extends GraphQLServiceProvider
{
    public function registerGraphQL(): void
    {
        $this->app->singleton('graphql', function (Container $app): GraphQL {
            $graphql = new GraphQL($app);

            $this->applySecurityRules();

            $this->bootSchemas($graphql);

            return $graphql;
        });

        $this->app->afterResolving('graphql', function (GraphQL $graphQL) {
            $this->bootTypes($graphQL);
        });
    }
}

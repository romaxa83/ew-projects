<?php

declare(strict_types=1);

namespace Wezom\Core\GraphQL\Directives;

use Nuwave\Lighthouse\Execution\ModelsLoader\ModelsLoader;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Directives\WithDirective;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Wezom\Core\GraphQL\NestedSimpleModelsLoader;

class NestedWithDirective extends WithDirective
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Eager-load an Eloquent relation.
"""
directive @nestedWith(
  """
  Specify the relationship method name in the model class,
  if it is named different from the field in the schema.
  """
  relation: String

  """
  Apply scopes to the underlying query.
  """
  scopes: [String!]
) repeatable on FIELD_DEFINITION
GRAPHQL;
    }

    protected function modelsLoader(
        mixed $parent,
        array $args,
        GraphQLContext $context,
        ResolveInfo $resolveInfo
    ): ModelsLoader {
        return new NestedSimpleModelsLoader(
            $this->relation(),
            $this->makeBuilderDecorator($parent, $args, $context, $resolveInfo),
        );
    }
}

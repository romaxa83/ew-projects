<?php

namespace Wezom\Core\GraphQL\Directives;

use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Wezom\Core\Scopes\ActiveGlobalScope;

class ActiveDirective extends BaseDirective implements FieldMiddleware
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Enable global scope for filtering by active field.
"""
directive @active on FIELD_DEFINITION
GRAPHQL;
    }

    /** Wrap around the final field resolver. */
    public function handleField(FieldValue $fieldValue): void
    {
        $fieldValue->wrapResolver(
            fn (callable $resolver) => function (
                mixed $root,
                array $args,
                GraphQLContext $context,
                ResolveInfo $info
            ) use ($resolver): mixed {
                try {
                    ActiveGlobalScope::enable();

                    // Call the resolver, passing along the resolver arguments
                    return $resolver($root, $args, $context, $info);
                } finally {
                    ActiveGlobalScope::disable();
                }
            }
        );
    }
}

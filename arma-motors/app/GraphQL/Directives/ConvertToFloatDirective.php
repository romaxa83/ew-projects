<?php

namespace App\GraphQL\Directives;

use App\Helpers\ConvertNumber;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ConvertToFloatDirective extends BaseDirective implements FieldMiddleware
{
    /**
     * Name of the directive as used in the schema.
     *
     * @return string
     */
    public function name(): string
    {
        return 'convertToFloat';
    }

    /**
     * Wrap around the final field resolver.
     *
     * @param \Nuwave\Lighthouse\Schema\Values\FieldValue $fieldValue
     * @param \Closure $next
     * @return \Nuwave\Lighthouse\Schema\Values\FieldValue
     */
    public function handleField(FieldValue $fieldValue, Closure $next): FieldValue
    {
        $previousResolver = $fieldValue->getResolver();

        return $next(
            $fieldValue->setResolver(
                function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($previousResolver) {

                    $root[camel_to_snake($resolveInfo->fieldName)] = ConvertNumber::fromNumberToFloat($root[camel_to_snake($resolveInfo->fieldName)]);

                    return $previousResolver($root, $args, $context, $resolveInfo);
                }
            )
        );
    }
}

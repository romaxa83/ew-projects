<?php

namespace Wezom\Core\ExtendPackage\Lighthouse;

use GraphQL\Type\Definition\ResolveInfo as BaseResolveInfo;
use Illuminate\Container\Container;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSetFactory;
use Nuwave\Lighthouse\Execution\Utils\FieldPath;
use Nuwave\Lighthouse\Schema\Values\FieldValue as BaseFieldValue;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class FieldValue extends BaseFieldValue
{
    public function finishResolver(callable $resolver): callable
    {
        // We expect the wrapped resolvers to run in order, but nesting them causes the last
        // applied wrapper to be run first. Thus, we reverse the wrappers before applying them.
        foreach (array_reverse($this->resolverWrappers) as $wrapper) {
            $resolver = $wrapper($resolver);
        }

        return function (
            mixed $root,
            array $baseArgs,
            GraphQLContext $context,
            BaseResolveInfo $baseResolveInfo
        ) use ($resolver): mixed {
            $path = FieldPath::withoutLists($baseResolveInfo->path);

            if (!isset(self::$transformedResolveArgs[$path])) {
                $argumentSetFactory = Container::getInstance()->make(ArgumentSetFactory::class);
                /** @phpstan-ignore-next-line */
                assert($argumentSetFactory instanceof ArgumentSetFactory);

                $argumentSet = $argumentSetFactory->fromResolveInfo($baseArgs, $baseResolveInfo);
                foreach ($this->argumentSetTransformers as $transform) {
                    $argumentSet = $transform($argumentSet, $baseResolveInfo);
                }

                $args = $argumentSet->toArray();

                self::$transformedResolveArgs[$path] = [$args, $argumentSet];
            } else {
                [$args, $argumentSet] = self::$transformedResolveArgs[$path];
            }

            return ($resolver)($root, $args, $context, new ResolveInfo($baseResolveInfo, $argumentSet));
        };
    }
}

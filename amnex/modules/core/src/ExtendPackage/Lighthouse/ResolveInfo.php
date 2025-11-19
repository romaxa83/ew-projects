<?php

namespace Wezom\Core\ExtendPackage\Lighthouse;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use Nuwave\Lighthouse\Execution\ResolveInfo as BaseResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ResolveInfo extends BaseResolveInfo
{
    public function enhanceBuilder(
        /** @phpstan-ignore-next-line  */
        QueryBuilder|EloquentBuilder|Relation|ScoutBuilder $builder,
        array $scopes,
        mixed $root,
        array $args,
        GraphQLContext $context,
        BaseResolveInfo $resolveInfo,
        ?callable $directiveFilter = null,
        /** @phpstan-ignore-next-line  */
    ): QueryBuilder|EloquentBuilder|Relation|ScoutBuilder {
        /** @phpstan-ignore-next-line  */
        if ($builder->getModel()->hasNamedScope('active') && has_root_directive($resolveInfo, 'active')) {
            $scopes[] = 'active';
        }

        return parent::enhanceBuilder($builder, $scopes, $root, $args, $context, $resolveInfo);
    }
}

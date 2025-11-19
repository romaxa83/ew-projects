<?php

namespace Wezom\Core\GraphQL\Directives;

use Exception;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Nuwave\Lighthouse\Exceptions\AuthorizationException;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ListType;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective as LighthouseBaseDirective;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Wezom\Core\Enums\PermissionActionEnum;
use Wezom\Core\Exceptions\Auth\PermissionNotRegisteredException;
use Wezom\Core\Permissions\Ability;
use Wezom\Core\Services\AbilityCheckerService;
use Wezom\Core\Services\AuthenticationService;

abstract class BaseDirective extends LighthouseBaseDirective
{
    public function __construct(protected Gate $gate, protected AbilityCheckerService $abilityCheckerService)
    {
    }

    protected function authenticate(array|string $guards, GraphQLContext $context): void
    {
        app(AuthenticationService::class)->authenticate($guards, $context);
    }

    /**
     * @throws AuthorizationException
     * @throws PermissionNotRegisteredException
     */
    protected function checkAbility(
        ?Authenticatable $user,
        string $modelClass,
        PermissionActionEnum|string $action,
        array $ids,
    ): void {
        if ($ability = $this->directiveArgValue('ability')) {
            $abilities = str($ability)
                ->explode(AbilityCheckerService::OR_DELIMITER)
                ->map(static fn ($v) => trim($v))
                ->filter()
                ->all();
        } else {
            $abilities = [Ability::toModel($modelClass)->action($action)];
        }

        $this->abilityCheckerService->inspect(
            $user,
            $abilities,
            $ids
        );
    }

    protected function assertHasIdArgument(string $name, array $args, ResolveInfo $resolveInfo): void
    {
        if (!array_key_exists($name, $args)) {
            throw new DefinitionException("Missing required '$name' argument of type ID!");
        }

        /** @var Argument $argument */
        $argument = $resolveInfo->argumentSet->arguments[$name];
        if ($argument->namedType()->name !== Type::ID) {
            throw new DefinitionException("The '$name' argument must be of type ID!");
        }
    }

    protected function assertHasIdListArgument(string $name, array $args, ResolveInfo $resolveInfo): void
    {
        if (!array_key_exists($name, $args)) {
            throw new DefinitionException("Missing required '$name' argument of type [ID!]!");
        }

        /** @var Argument $argument */
        $argument = $resolveInfo->argumentSet->arguments[$name];
        if (!($argument->type instanceof ListType) || $argument->namedType()->name !== Type::ID) {
            throw new DefinitionException("The '$name' argument must be of type [ID!]!");
        }
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $model
     * @return Collection<T>
     *
     * @throws Exception
     */
    protected function getModels(string $model, array $ids): Collection
    {
        $entities = $model::query()->findMany($ids);

        if ($entities->count() != count($ids)) {
            throw new InvalidArgumentException();
        }

        return $entities;
    }
}

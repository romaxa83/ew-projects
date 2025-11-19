<?php

namespace Wezom\Core\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Nuwave\Lighthouse\Exceptions\AuthorizationException as LighthouseAuthorizationException;
use Wezom\Core\Enums\PermissionActionEnum;
use Wezom\Core\Exceptions\Auth\PermissionNotRegisteredException;
use Wezom\Core\Permissions\Ability;
use Wezom\Core\Permissions\PermissionsManager;

class AbilityCheckerService
{
    public const OR_DELIMITER = '||';

    /**
     * @throws LighthouseAuthorizationException
     * @throws PermissionNotRegisteredException
     */
    public function inspect(?Authenticatable $user, array $abilities, array|int|string|null $ids = null): void
    {
        /** @var Gate $gate */
        $gate = \Gate::forUser($user);

        /** @var Collection<AuthorizationException> $errors */
        $errors = collect();
        foreach ($abilities as $oneAbility) {
            try {
                $this->authorizeAbility($oneAbility, $gate, $ids);
            } catch (AuthorizationException $e) {
                $errors->add($e);
            }
        }

        // If all abilities has errors - throw exception
        if ($errors->count() === count($abilities)) {
            foreach ($errors as $error) {
                if ($error->getMessage()) {
                    throw LighthouseAuthorizationException::fromLaravel($error);
                }
            }

            throw LighthouseAuthorizationException::fromLaravel($errors->first());
        }
    }

    /**
     * @throws AuthorizationException
     * @throws PermissionNotRegisteredException
     */
    private function authorizeAbility(mixed $ability, Gate $gate, array|int|string|null $ids = null): void
    {
        /** @var null|class-string<Model> $modelClass */
        $modelClass = null;
        $action = $ability;
        if ($ability instanceof Ability) {
            $modelClass = $ability->getModel();

            $action = $ability->getAction();
            if ($action instanceof PermissionActionEnum) {
                $action = $action->value;
            }

            $ability = $ability->build();
        }

        $policy = $gate->getPolicyFor($modelClass);
        if ($policy && method_exists($policy, camel_case($action))) {
            $models = $modelClass::query()->find(array_wrap($ids));

            foreach ($models as $model) {
                $this->checkOneAbility($gate, $action, $model);
            }

            return;
        }

        app(PermissionsManager::class)->guard(auth()->getDefaultDriver())->checkExists($ability);

        $this->checkOneAbility($gate, $ability, $modelClass);
    }

    /**
     * @throws AuthorizationException
     */
    protected function checkOneAbility(Gate $gate, string $ability, mixed $arg): void
    {
        $gate->inspect($ability, [$arg])->authorize();
    }
}

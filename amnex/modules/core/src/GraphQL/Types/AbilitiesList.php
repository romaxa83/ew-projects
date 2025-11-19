<?php

namespace Wezom\Core\GraphQL\Types;

use Cache;
use Gate;
use Illuminate\Database\Eloquent\Model;
use Wezom\Core\Exceptions\TranslatedException;

/**
 * @property-read bool $update
 * @property-read bool $delete
 */
class AbilitiesList
{
    /**
     * @param  bool  $checkPolicies  Disabling policies check can add more verbosity and|or skip expensive list checks
     */
    public function __construct(
        protected Model $model,
        protected string $abilityPrefix,
        protected bool $checkPolicies = true
    ) {
    }

    public function __get(string $name)
    {
        return method_exists($this, $name) ? $this->$name() : $this->checkPermission($this->model, $name);
    }

    protected function checkPermission(Model $model, string $permission): bool
    {
        if ($this->checkPolicies) {
            try {
                $result = Gate::raw($permission, [$model]);
                if ($result !== null) {
                    return $result;
                }
            } catch (TranslatedException $e) {
                return false;
            }
        }

        $permissionKey = $this->abilityPrefix . '.' . kebab_case($permission);

        return Cache::driver('array')->rememberForever(
            'permissions-check-' . $permissionKey,
            function () use ($permissionKey) {
                return Gate::check($permissionKey);
            }
        );
    }
}

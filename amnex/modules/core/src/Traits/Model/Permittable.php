<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Model;

use Str;
use Wezom\Core\GraphQL\Types\AbilitiesList;

trait Permittable
{
    public function getAbilitiesAttribute(): AbilitiesList
    {
        $class = $this->getAbilitiesClass();

        return new $class($this, $this->getAbilityPrefix(), $this->checkAbilitiesPolicy());
    }

    public function getAbilityPrefix(): string
    {
        return property_exists($this, 'abilityPrefix')
            ? $this->abilityPrefix
            : Str::of(class_basename(static::class))->plural()->kebab()->toString();
    }

    /**
     * @return class-string<AbilitiesList>
     */
    protected function getAbilitiesClass(): string
    {
        return AbilitiesList::class;
    }

    protected function checkAbilitiesPolicy(): bool
    {
        return config('permissions.abilities_list_checks_policies');
    }
}

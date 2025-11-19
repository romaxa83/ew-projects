<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Model;

use Illuminate\Database\Eloquent\Builder;
use Wezom\Core\Scopes\ActiveGlobalScope;

/**
 * @property bool $active
 *
 * @see ActiveScopeTrait::scopeActive()
 *
 * @method static active(bool $value = true)
 */
trait ActiveScopeTrait
{
    protected static function bootActiveScopeTrait(): void
    {
        static::addGlobalScope(new ActiveGlobalScope());
    }

    public function scopeActive(Builder|self $builder, bool|array $value = true): void
    {
        if ($this->hasActiveWhere($builder)) {
            return;
        }

        $value = is_array($value) ? true : $value;

        $builder->where($this->qualifyColumn('active'), $value);
    }

    protected function hasActiveWhere(Builder|self $builder): bool
    {
        $wheres = $builder->getQuery()->wheres;
        if (!$wheres) {
            return false;
        }

        $activeColumns = ['active', $builder->getModel()->qualifyColumn('active')];
        foreach ($wheres as $where) {
            $column = $where['column'] ?? null;
            if ($column && in_array($column, $activeColumns)) {
                return true;
            }
        }

        return false;
    }

    public function isInactive(): bool
    {
        return !$this->isActive();
    }

    public function isActive(): bool
    {
        return (bool)$this->active;
    }

    public function toggleActive(): static
    {
        $this->active = !$this->active;

        return $this;
    }
}

<?php

namespace App\Traits\Model;

use App\Contracts\Models\HasGuard;
use App\Enums\Permissions\GuardsEnum;
use Illuminate\Database\Eloquent\Builder;

trait ActiveScopeTrait
{
    public function scopeActive(Builder|self $b, bool $value = true): void
    {
        $b->where($this->getTable() . '.active', $value);
    }

    public function scopeActiveGuard(Builder $builder, HasGuard $user): void
    {
        if ($user->getGuard() === GuardsEnum::ADMIN) {
            return;
        }

        $builder->where('active', true);
    }

    public function toggleActive(): static
    {
        $this->active = !$this->active;

        return $this;
    }
}

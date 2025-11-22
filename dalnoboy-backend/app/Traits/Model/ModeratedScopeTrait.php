<?php


namespace App\Traits\Model;


use App\Contracts\Models\HasGuard;
use App\Enums\Permissions\GuardsEnum;
use Illuminate\Database\Eloquent\Builder;

trait ModeratedScopeTrait
{
    public function scopeModerated(Builder $builder, HasGuard $guard): void
    {
        if ($guard->getGuard() === GuardsEnum::USER) {
            $builder->where('is_moderated', true);
        }
    }

    public function scopeFilterModerated(Builder $builder, bool $isModerated = true): void
    {
        $builder->where('is_moderated', $isModerated);
    }

    public function isModerated(): bool
    {
        return $this->is_moderated;
    }
}

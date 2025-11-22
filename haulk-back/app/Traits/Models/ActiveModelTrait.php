<?php

namespace App\Traits\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait ActiveTrait
 *
 * @property bool status
 *
 * @method static Builder active(bool $status = true)
 *
 * @package App\Traits\Models
 */
trait ActiveModelTrait
{
    /**
     * @param Builder $builder
     * @param bool $status
     */
    public function scopeActive($builder, $status = true)
    {
        $builder->where('status', $status);
    }

    public function setActive()
    {
        $this->status = true;

        return $this;
    }

    public function setDisable()
    {
        $this->status = false;

        return $this;
    }
}

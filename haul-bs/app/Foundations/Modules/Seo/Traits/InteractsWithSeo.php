<?php

namespace App\Foundations\Modules\Seo\Traits;

use App\Foundations\Modules\Seo\Models\Seo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @see static::seo()
 * @property Seo|null|MorphOne seo
 */
trait InteractsWithSeo
{
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'model');
    }
}


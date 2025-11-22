<?php

namespace App\Foundations\Modules\Seo\Repositories;

use App\Foundations\Modules\Seo\Models\Seo;
use App\Foundations\Repositories\BaseEloquentRepository;

final readonly class SeoRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Seo::class;
    }
}

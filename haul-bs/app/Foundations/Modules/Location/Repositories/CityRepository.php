<?php

namespace App\Foundations\Modules\Location\Repositories;

use App\Foundations\Http\Requests\Common\SearchRequest;
use App\Foundations\Modules\Location\Models\City;
use Illuminate\Database\Eloquent\Collection;

final readonly class CityRepository
{
    public function getList(array $filters = []): Collection
    {
        return City::filter($filters)
            ->with(['state'])
            ->orderBy('country_code', 'asc')
            ->orderBy('name', 'asc')
            ->orderBy('zip', 'asc')
            ->limit($filters['limit'] ?? SearchRequest::DEFAULT_LIMIT)
            ->get();
    }
}

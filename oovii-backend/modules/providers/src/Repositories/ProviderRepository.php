<?php

namespace WezomCms\Providers\Repositories;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Providers\Models\Provider;
use WezomCms\Providers\Types\ProviderStatus;

class ProviderRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Provider::query();
    }

    public function countByStatus(ProviderStatus $status): int
    {
        return $this->query()->where('status', $status->getValue())->count();
    }

    public function getProvidersWithOrdersForSelect(): array
    {
        return $this->query()
            ->whereHas('orders')
            ->pluck('name', 'id')
            ->toArray();
    }
}

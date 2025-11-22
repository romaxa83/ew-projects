<?php

namespace App\GraphQL\Mutations\BackOffice\Tires;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Tires\Tire;
use App\Permissions\Tires\TireUpdatePermission;
use App\Services\Tires\TireService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class TireToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'tireToggleActive';
    public const PERMISSION = TireUpdatePermission::KEY;

    public function __construct(private TireService $service)
    {
        $this->setAdminGuard();
    }

    protected function getEntities(array $ids): Collection
    {
        return $this->service->getByIds($ids);
    }

    protected function action(iterable $entities): void
    {
        $this->service->toggleActiveMany($entities);
    }

    protected function getAdditionalRule(array $args): mixed
    {
        return Rule::exists(Tire::class, 'id');
    }
}

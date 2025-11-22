<?php

namespace App\GraphQL\Mutations\BackOffice\Vehicles;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Vehicles\Vehicle;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Vehicles\VehicleService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class VehicleToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'vehicleToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private VehicleService $service)
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
        return Rule::exists(Vehicle::class, 'id');
    }
}

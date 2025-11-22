<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleTypes;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\VehicleType;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\VehicleTypeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class VehicleTypeToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'vehicleTypeToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private VehicleTypeService $service)
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
        return Rule::exists(VehicleType::class, 'id');
    }
}

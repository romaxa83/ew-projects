<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleModels;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\VehicleModel;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\VehicleModelService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class VehicleModelToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'vehicleModelToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private VehicleModelService $service)
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
        return Rule::exists(VehicleModel::class, 'id');
    }
}

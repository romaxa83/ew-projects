<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleClasses;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\VehicleClass;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\VehicleClassService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class VehicleClassToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'vehicleClassToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private VehicleClassService $service)
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
        return Rule::exists(VehicleClass::class, 'id');
    }
}

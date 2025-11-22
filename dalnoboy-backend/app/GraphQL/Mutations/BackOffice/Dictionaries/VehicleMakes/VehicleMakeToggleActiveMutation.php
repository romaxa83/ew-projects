<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleMakes;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\VehicleMake;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\VehicleMakeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class VehicleMakeToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'vehicleMakeToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private VehicleMakeService $service)
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
        return Rule::exists(VehicleMake::class, 'id');
    }
}

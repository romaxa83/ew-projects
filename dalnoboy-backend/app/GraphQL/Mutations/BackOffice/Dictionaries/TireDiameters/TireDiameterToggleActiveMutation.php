<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireDiameters;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\TireDiameter;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireDiameterService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class TireDiameterToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'tireDiameterToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireDiameterService $service)
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
        return Rule::exists(TireDiameter::class, 'id');
    }
}

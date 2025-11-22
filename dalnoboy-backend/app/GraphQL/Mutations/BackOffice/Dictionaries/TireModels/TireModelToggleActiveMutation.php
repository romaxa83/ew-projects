<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireModels;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\TireModel;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireModelService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class TireModelToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'tireModelToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireModelService $service)
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
        return Rule::exists(TireModel::class, 'id');
    }
}

<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireTypes;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\TireType;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireTypeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class TireTypeToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'tireTypeToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireTypeService $service)
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
        return Rule::exists(TireType::class, 'id');
    }
}

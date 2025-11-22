<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireRelationshipTypes;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\TireRelationshipType;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireRelationshipTypeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class TireRelationshipTypeToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'tireRelationshipTypeToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireRelationshipTypeService $service)
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
        return Rule::exists(TireRelationshipType::class, 'id');
    }
}

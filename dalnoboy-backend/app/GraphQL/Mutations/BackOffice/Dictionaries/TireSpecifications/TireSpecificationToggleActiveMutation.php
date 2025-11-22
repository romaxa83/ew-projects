<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireSpecifications;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\TireSpecification;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireSpecificationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class TireSpecificationToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'tireSpecificationToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireSpecificationService $service)
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
        return Rule::exists(TireSpecification::class, 'id');
    }
}

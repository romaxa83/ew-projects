<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\Regulations;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\Regulation;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\RegulationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class RegulationToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'regulationToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private RegulationService $service)
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
        return Rule::exists(Regulation::class, 'id');
    }
}

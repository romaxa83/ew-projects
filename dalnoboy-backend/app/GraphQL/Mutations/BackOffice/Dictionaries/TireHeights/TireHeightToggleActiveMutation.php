<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireHeights;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\TireHeight;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireHeightService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class TireHeightToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'tireHeightToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireHeightService $service)
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
        return Rule::exists(TireHeight::class, 'id');
    }
}

<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireSizes;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\TireSize;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireSizeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class TireSizeToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'tireSizeToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireSizeService $service)
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
        return Rule::exists(TireSize::class, 'id');
    }
}

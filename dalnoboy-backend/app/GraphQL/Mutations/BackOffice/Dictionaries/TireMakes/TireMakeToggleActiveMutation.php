<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireMakes;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\TireMake;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireMakeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class TireMakeToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'tireMakeToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireMakeService $service)
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
        return Rule::exists(TireMake::class, 'id');
    }
}

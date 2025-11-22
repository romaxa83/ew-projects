<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireWidths;

use App\GraphQL\Mutations\BaseActionMutation;
use App\Models\Dictionaries\TireWidth;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireWidthService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class TireWidthToggleActiveMutation extends BaseActionMutation
{
    public const NAME = 'tireWidthToggleActive';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireWidthService $service)
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

    protected function getAdditionalRule(array $args): Exists
    {
        return Rule::exists(TireWidth::class, 'id');
    }
}

<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Manuals\Groups;

use App\GraphQL\Types\Catalog\Manuals\ManualGroupType;
use App\Rules\TranslationsArrayValidator;
use App\Services\Catalog\Manuals\ManualGroupService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\Type;

abstract class BaseManualGroupMutation extends BaseMutation
{
    public function __construct(protected ManualGroupService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ManualGroupType::type();
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            [
                'manual_group.translations' => ['required', 'array', new TranslationsArrayValidator()],
            ]
        );
    }
}

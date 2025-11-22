<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Manuals\Groups;

use App\Dto\Catalog\Manuals\ManualGroupDto;
use App\GraphQL\InputTypes\Catalog\Manuals\ManualGroupUpdateInput;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Permissions\Catalog\Manuals\ManualUpdatePermission;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ManualGroupUpdateMutation extends BaseManualGroupMutation
{
    public const NAME = 'manualGroupUpdate';
    public const PERMISSION = ManualUpdatePermission::KEY;

    public function args(): array
    {
        return [
            'manual_group' => [
                'type' => ManualGroupUpdateInput::nonNullType(),
            ]
        ];
    }

    /** @throws Throwable */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ManualGroup {
        return makeTransaction(
            fn() => $this->service->update(
                ManualGroup::query()->findOrFail($args['manual_group']['id']),
                ManualGroupDto::byArgs($args['manual_group'])
            )
        );
    }

    public function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            array_merge(
                [
                    'manual_group.id' => ['required', 'integer', Rule::exists(ManualGroup::TABLE, 'id')],
                ],
                parent::rules($args)
            )
        );
    }
}

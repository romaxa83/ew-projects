<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Manuals\Groups;

use App\Dto\Catalog\Manuals\ManualGroupDto;
use App\GraphQL\InputTypes\Catalog\Manuals\ManualGroupCreateInput;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Permissions\Catalog\Manuals\ManualCreatePermission;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ManualGroupCreateMutation extends BaseManualGroupMutation
{
    public const NAME = 'manualGroupCreate';
    public const PERMISSION = ManualCreatePermission::KEY;

    public function args(): array
    {
        return [
            'manual_group' => [
                'type' => ManualGroupCreateInput::nonNullType(),
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
    ): ManualGroup
    {
        return makeTransaction(
            fn() => $this->service->create(
                ManualGroupDto::byArgs($args['manual_group'])
            )
        );
    }
}

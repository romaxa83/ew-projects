<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Manuals;

use App\Dto\Catalog\Manuals\ManualDto;
use App\GraphQL\InputTypes\Catalog\Manuals\ManualUpdateInput;
use App\GraphQL\Types\Catalog\Manuals\ManualType;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Permissions\Catalog\Manuals\ManualUpdatePermission;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

class ManualUpdateMutation extends BaseManualMutation
{
    public const NAME = 'manualUpdate';
    public const PERMISSION = ManualUpdatePermission::KEY;

    public function args(): array
    {
        return [
            'manual' => ManualUpdateInput::nonNullType(),
        ];
    }

    public function type(): Type
    {
        return ManualType::type();
    }

    /**
     * @throws Throwable
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Manual {
        return makeTransaction(
            fn() => $this->service->update(
                Manual::query()->findOrFail($args['manual']['manual_id']),
                ManualDto::byArgs($args['manual']),
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'manual.manual_group_id' => ['required', 'integer', Rule::exists(ManualGroup::TABLE, 'id')],
            'manual.manual_id' => ['required', 'integer', Rule::exists(Manual::TABLE, 'id')],
            'manual.pdf' => ['required', 'mimes:pdf'],
        ];
    }
}

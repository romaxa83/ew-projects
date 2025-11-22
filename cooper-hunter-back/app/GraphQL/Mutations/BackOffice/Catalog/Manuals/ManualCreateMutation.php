<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Manuals;

use App\Dto\Catalog\Manuals\ManualListDto;
use App\GraphQL\InputTypes\Catalog\Manuals\ManualCreateInput;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Permissions\Catalog\Manuals\ManualCreatePermission;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

class ManualCreateMutation extends BaseManualMutation
{
    public const NAME = 'manualCreate';
    public const PERMISSION = ManualCreatePermission::KEY;

    public function args(): array
    {
        return [
            'manuals' => ManualCreateInput::nonNullList()
        ];
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
    ): Collection {
        return makeTransaction(
            fn() => $this->service->createMany(ManualListDto::byArgs($args['manuals']))
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'manuals.*.manual_group_id' => ['required', 'integer', Rule::exists(ManualGroup::TABLE, 'id')],
            'manuals.*.pdf' => ['required', 'mimes:pdf'],
        ];
    }
}

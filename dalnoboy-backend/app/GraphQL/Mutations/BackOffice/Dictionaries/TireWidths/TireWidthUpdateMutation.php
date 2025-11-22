<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireWidths;

use App\Dto\Dictionaries\TireWidthDto;
use App\GraphQL\InputTypes\Dictionaries\TireWidthInputType;
use App\GraphQL\Types\Dictionaries\TireWidthType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireWidth;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\TireWidthService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TireWidthUpdateMutation extends BaseMutation
{
    public const NAME = 'tireWidthUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private TireWidthService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return TireWidthType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(TireWidth::class, 'id')
                ]
            ],
            'tire_width' => [
                'type' => TireWidthInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return TireWidth
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): TireWidth {
        return makeTransaction(
            fn() => $this->service->update(
                TireWidthDto::byArgs($args['tire_width']),
                TireWidth::find($args['id'])
            )
        );
    }
}

<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot;

use App\Dto\Catalog\Troubleshoots\TroubleshootDto;
use App\GraphQL\Types\Catalog\Troubleshoots\Troubleshoots;
use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Troubleshoots\Group;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use App\Permissions\Catalog\Troubleshoots\Troubleshoot\UpdatePermission;
use App\Services\Catalog\Troubleshoots\TroubleshootService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

class TroubleshootUpdateMutation extends BaseMutation
{
    public const NAME = 'troubleshootUpdate';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(private TroubleshootService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Troubleshoots\TroubleshootType::type();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Troubleshoot::class, 'id')
                ]
            ],
            'active' => [
                'type' => Type::boolean()
            ],
            'name' => [
                'type' => NonNullType::string()
            ],
            'group_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'integer',
                    Rule::exists(Group::class, 'id')
                ]
            ],
            'pdf' => [
                'type' => FileType::type(),
                'rules' => [
                    'nullable',
                    'mimes:pdf'
                ]
            ],
        ];
    }

    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Troubleshoot
     * @throws Throwable
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Troubleshoot
    {
        return makeTransaction(
            fn() => $this->service->update(
                TroubleshootDto::byArgs($args),
                Troubleshoot::find($args['id'])
            )
        );
    }
}

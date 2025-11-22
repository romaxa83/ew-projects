<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Groups;

use App\Dto\Catalog\Troubleshoots\GroupDto;
use App\GraphQL\Types\Catalog\Troubleshoots\Groups;
use App\Models\Catalog\Troubleshoots\Group;
use App\Permissions\Catalog\Troubleshoots\Group\CreatePermission;
use App\Rules\TranslationsArrayValidator;
use App\Services\Catalog\Troubleshoots\GroupService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TroubleshootGroupCreateMutation extends BaseMutation
{
    public const NAME = 'troubleshootGroupCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(protected GroupService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Groups\TroubleshootGroupType::type();
    }

    public function args(): array
    {
        return [
            'active' => [
                'type' => Type::boolean(),
            ],
            'translations' => [
                'type' => Groups\TranslateInputType::nonNullList(),
                'rules' => [
                    new TranslationsArrayValidator()
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
     * @return Group
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Group
    {
        return makeTransaction(
            fn() => $this->service->create(
                GroupDto::byArgs($args)
            )
        );
    }
}


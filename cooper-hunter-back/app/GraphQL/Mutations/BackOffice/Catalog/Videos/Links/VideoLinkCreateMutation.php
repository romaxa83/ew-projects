<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Videos\Links;

use App\Dto\Catalog\Video\LinkDto;
use App\GraphQL\Types\Catalog\Videos\Links;
use App\GraphQL\Types\Catalog\Videos\Links\VideoLinkTypeEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Videos\Group;
use App\Models\Catalog\Videos\VideoLink;
use App\Permissions\Catalog\Videos\Link\CreatePermission;
use App\Rules\TranslationsArrayValidator;
use App\Services\Catalog\Video\LinkService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VideoLinkCreateMutation extends BaseMutation
{
    public const NAME = 'videoLinkCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(protected LinkService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Links\VideoLinkType::type();
    }

    public function args(): array
    {
        return [
            'active' => [
                'type' => Type::boolean(),
            ],
            'link_type' => [
                'type' => VideoLinkTypeEnumType::nonNullType(),
            ],
            'link' => [
                'type' => NonNullType::string(),
            ],
            'group_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Group::class, 'id')
                ]
            ],
            'translations' => [
                'type' => Links\TranslateInputType::nonNullList(),
                'rules' => [
                    'required',
                    'array',
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
     * @return VideoLink
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): VideoLink
    {
        return makeTransaction(
            fn() => $this->service->create(
                LinkDto::byArgs($args)
            )
        );
    }
}

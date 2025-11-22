<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Videos\Links;

use App\Dto\Catalog\Video\LinkDto;
use App\GraphQL\Types\Catalog\Videos\Links;
use App\GraphQL\Types\Catalog\Videos\Links\VideoLinkTypeEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Videos\VideoLink;
use App\Permissions\Catalog\Videos\Link\UpdatePermission;
use App\Rules\TranslationsArrayValidator;
use App\Services\Catalog\Video\LinkService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VideoLinkUpdateMutation extends BaseMutation
{
    public const NAME = 'videoLinkUpdate';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(private LinkService $service,)
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
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    Rule::exists(VideoLink::class, 'id')
                ]
            ],
            'link_type' => [
                'type' => VideoLinkTypeEnumType::nonNullType(),
            ],
            'active' => [
                'type' => Type::boolean()
            ],
            'link' => [
                'type' => NonNullType::string()
            ],
            'group_id' => [
                'type' => NonNullType::id()
            ],
            'translations' => [
                'type' => Links\TranslateInputType::nonNullList(),
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
     * @return VideoLink
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): VideoLink
    {
        return makeTransaction(
            fn() => $this->service->update(
                LinkDto::byArgs($args),
                VideoLink::find($args['id'])
            )
        );
    }
}

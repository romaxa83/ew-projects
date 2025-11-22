<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Videos\Links;

use App\GraphQL\Types\Catalog\Videos\Links;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Videos\VideoLink;
use App\Permissions\Catalog\Videos\Link\UpdatePermission;
use App\Services\Catalog\Video\LinkService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VideoLinkToggleActiveMutation extends BaseMutation
{
    public const NAME = 'videoLinkToggleActive';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(private LinkService $service)
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
                    'required',
                    'int',
                    Rule::exists(VideoLink::class, 'id')
                ]
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return VideoLink
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): VideoLink
    {
        return makeTransaction(
            fn () => $this->service->toggleActive(
                VideoLink::find($args['id'])
            )
        );
    }
}


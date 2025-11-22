<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Videos\Links;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Videos\VideoLink;
use App\Permissions\Catalog\Videos\Link\DeletePermission;
use App\Services\Catalog\Video\LinkService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class VideoLinkDeleteMutation extends BaseMutation
{
    public const NAME = 'videoLinkDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(private LinkService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::type();
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

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): ResponseMessageEntity
    {
        try {
            $this->service->remove(
                VideoLink::find($args['id'])
            );
            return ResponseMessageEntity::success(__('messages.catalog.video.link.actions.delete.success.one-entity'));
        } catch (\Throwable) {
            return ResponseMessageEntity::fail(__('Oops, something went wrong!'));
        }
    }
}



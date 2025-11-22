<?php

namespace App\GraphQL\Mutations\BackOffice\News\Videos;

use App\GraphQL\Types\News\VideoType;
use App\GraphQL\Types\NonNullType;
use App\Models\News\Video;
use App\Permissions\News\Videos\VideoUpdatePermission;
use App\Services\News\VideoService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class VideoToggleActiveMutation extends BaseMutation
{
    public const NAME = 'videoToggleActive';
    public const PERMISSION = VideoUpdatePermission::KEY;

    public function __construct(protected VideoService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return VideoType::type();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Video
    {
        return $this->service->toggle(Video::query()->find($args['id']));
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Video::TABLE, 'id')],
        ];
    }

}

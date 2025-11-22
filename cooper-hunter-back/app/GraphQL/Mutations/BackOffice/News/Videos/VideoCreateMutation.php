<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\News\Videos;

use App\Dto\News\VideoDto;
use App\GraphQL\InputTypes\News\Videos\VideoCreateInput;
use App\GraphQL\Types\News\VideoType;
use App\Models\News\Video;
use App\Permissions\News\Videos\VideoCreatePermission;
use App\Services\News\VideoService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VideoCreateMutation extends BaseMutation
{
    public const NAME = 'videoCreate';
    public const PERMISSION = VideoCreatePermission::KEY;

    public function __construct(private VideoService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return VideoType::nonNullType();
    }

    public function args(): array
    {
        return [
            'video' => VideoCreateInput::nonNullType(),
        ];
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Video {
        return makeTransaction(
            fn() => $this->service->create(
                VideoDto::byArgs($args['video'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'video.slug' => [
                Rule::unique(Video::class, 'slug')
            ]
        ];
    }
}

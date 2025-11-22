<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\News\Videos;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\News\Video;
use App\Permissions\News\Videos\VideoDeletePermission;
use App\Services\News\VideoService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VideoDeleteMutation extends BaseMutation
{
    public const NAME = 'videoDelete';
    public const PERMISSION = VideoDeletePermission::KEY;

    public function __construct(private VideoService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
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
    ): ResponseMessageEntity {
        $video = Video::query()
            ->whereKey($args['id'])
            ->first();


        makeTransaction(fn() => $this->service->delete($video));

        return ResponseMessageEntity::success(__('Entity deleted'));
    }


    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn() => [
                'id' => ['required', 'integer', Rule::exists(Video::TABLE, 'id')],
            ]
        );
    }
}

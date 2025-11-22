<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\News;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\News\News;
use App\Permissions\News\NewsDeletePermission;
use App\Services\News\NewsService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class NewsDeleteMutation extends BaseMutation
{
    public const NAME = 'newsDelete';
    public const PERMISSION = NewsDeletePermission::KEY;

    public function __construct(private NewsService $service)
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
        $news = News::query()
            ->whereKey($args['id'])
            ->first();

        makeTransaction(fn() => $this->service->delete($news));

        return ResponseMessageEntity::success(__('Entity deleted'));
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn() => [
                'id' => ['required', 'integer', Rule::exists(News::TABLE, 'id')],
            ]
        );
    }
}

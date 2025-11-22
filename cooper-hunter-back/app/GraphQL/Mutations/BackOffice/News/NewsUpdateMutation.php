<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\News;

use App\Dto\News\NewsDto;
use App\GraphQL\InputTypes\News\NewsUpdateInput;
use App\GraphQL\Types\News\NewsType;
use App\Models\News\News;
use App\Permissions\News\NewsUpdatePermission;
use App\Services\News\NewsService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class NewsUpdateMutation extends BaseMutation
{
    public const NAME = 'newsUpdate';
    public const PERMISSION = NewsUpdatePermission::KEY;

    public function __construct(private NewsService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return NewsType::nonNullType();
    }

    public function args(): array
    {
        return [
            'news' => NewsUpdateInput::nonNullType(),
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
    ): News {
        return makeTransaction(
            fn() => $this->service->update(
                News::find($args['news']['id']),
                NewsDto::byArgs($args['news'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'news.slug' => [
                Rule::unique(News::class, 'slug')->ignore($args['news']['id'])
            ],
        ];
    }
}

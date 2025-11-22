<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\News;

use App\Dto\News\NewsDto;
use App\GraphQL\InputTypes\News\NewsCreateInput;
use App\GraphQL\Types\News\NewsType;
use App\Models\News\News;
use App\Models\News\Tag;
use App\Permissions\News\NewsCreatePermission;
use App\Services\News\NewsService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class NewsCreateMutation extends BaseMutation
{
    public const NAME = 'newsCreate';
    public const PERMISSION = NewsCreatePermission::KEY;

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
            'news' => NewsCreateInput::nonNullType(),
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
            fn() => $this->service->create(
                NewsDto::byArgs($args['news'])
            ),
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'news.slug' => [
                'required', 'string', Rule::unique(News::class, 'slug')
            ],
        ];
    }
}

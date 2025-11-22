<?php

namespace App\GraphQL\Mutations\BackOffice\News;

use App\GraphQL\Types\News\NewsType;
use App\GraphQL\Types\NonNullType;
use App\Models\News\News;
use App\Permissions\News\NewsUpdatePermission;
use App\Services\News\NewsService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class NewsToggleActiveMutation extends BaseMutation
{
    public const NAME = 'newsToggleActive';
    public const PERMISSION = NewsUpdatePermission::KEY;

    public function __construct(protected NewsService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return NewsType::type();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): News
    {
        $news = News::query()->findOrFail($args['id']);

        return $this->service->toggle($news);
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(News::TABLE, 'id')],
        ];
    }

}

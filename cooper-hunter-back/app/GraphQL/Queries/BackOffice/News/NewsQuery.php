<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\BackOffice\News;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\News\NewsType;
use App\Models\BaseModel;
use App\Models\News\News;
use App\Permissions\News\NewsListPermission;
use Core\GraphQL\Queries\GenericQuery;

class NewsQuery extends GenericQuery
{
    public const NAME = 'news';
    public const PERMISSION = NewsListPermission::KEY;

    protected BaseModel|string $model = News::class;
    protected BaseType|string $type = NewsType::class;

    public function args(): array
    {
        return array_merge(
            parent::args(),
            $this->getSlugsArgs(),
        );
    }

    public function __construct()
    {
        $this->setAdminGuard();
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            parent::rules(),
            $this->getSlugsRules()
        );
    }
}

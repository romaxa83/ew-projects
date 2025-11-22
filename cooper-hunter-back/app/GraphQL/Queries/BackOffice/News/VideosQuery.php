<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\BackOffice\News;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\News\VideoType;
use App\Models\BaseModel;
use App\Models\News\Video;
use App\Permissions\News\Videos\VideoListPermission;
use Core\GraphQL\Queries\GenericQuery;

class VideosQuery extends GenericQuery
{
    public const NAME = 'videos';
    public const PERMISSION = VideoListPermission::KEY;

    protected BaseModel|string $model = Video::class;
    protected BaseType|string $type = VideoType::class;

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

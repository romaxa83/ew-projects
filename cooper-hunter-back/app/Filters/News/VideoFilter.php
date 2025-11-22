<?php

declare(strict_types=1);

namespace App\Filters\News;

use App\Filters\BaseModelFilter;
use App\Models\News\Video;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SlugFilterTrait;
use App\Traits\Filter\SortFilterTrait;

/**
 * @mixin Video
 */
class VideoFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;
    use SlugFilterTrait;

    public const TABLE = Video::TABLE;
}

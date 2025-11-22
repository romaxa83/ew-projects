<?php

namespace App\Models\News;

use App\Filters\News\VideoFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\News\VideoFactory;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property bool active
 * @property int sort
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static VideoFactory factory(...$parameters)
 */
class Video extends BaseModel
{
    use HasTranslations;
    use HasFactory;
    use SetSortAfterCreate;
    use Filterable;

    public const TABLE = 'videos';
    public const MORPH_NAME = 'video';

    protected $table = self::TABLE;

    protected $fillable = [
        'sort',
        'slug',
        'created_at',
    ];

    public static function getAllowedSortingFields(): array
    {
        return [
            'id',
            'sort',
        ];
    }

    public function modelFilter(): string
    {
        return VideoFilter::class;
    }
}

<?php

namespace App\Models\News;

use App\Contracts\Media\HasMedia;
use App\Filters\News\NewsFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\News\NewsFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int tag_id
 * @property bool active
 * @property int sort
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static NewsFactory factory(...$parameters)
 */
class News extends BaseModel implements HasMedia
{
    use HasFactory;
    use SetSortAfterCreate;
    use HasTranslations;
    use InteractsWithMedia;
    use Filterable;

    public const TABLE = 'news';
    public const MORPH_NAME = 'news';
    public const MEDIA_COLLECTION_NAME = 'news';

    public const CONVERSIONS = [
        'thumbnail' => [
            'width' => 346 * 2,
            'height' => 231 * 2,
        ],
    ];

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
            'created_at',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->singleFile()
            ->acceptsMimeTypes($this->mimeImage());
    }

    public function modelFilter(): string
    {
        return NewsFilter::class;
    }

    public function tag(): BelongsTo|Tag
    {
        return $this->belongsTo(Tag::class);
    }
}

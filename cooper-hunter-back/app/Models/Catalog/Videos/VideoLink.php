<?php

namespace App\Models\Catalog\Videos;

use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use App\Filters\Catalog\Video\LinkFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use BenSampo\Enum\Traits\CastsEnums;
use Database\Factories\Catalog\Videos\VideoLinkFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int sort
 * @property bool active
 * @property string link
 * @property string link_type
 * @property int group_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see VideoLink::group()
 * @property-read Group|null group
 *
 * @method static VideoLinkFactory factory(...$options)
 */
class VideoLink extends BaseModel
{
    use HasTranslations;
    use Filterable;
    use HasFactory;
    use ActiveScopeTrait;
    use SetSortAfterCreate;
    use CastsEnums;

    public const TABLE = 'catalog_video_links';
    protected $table = self::TABLE;

    protected $fillable = [
        'sort',
        'active',
        'group_id',
        'link',
        'link_type',
    ];

    protected $casts = [
        'active' => 'boolean',
        'link_type' => VideoLinkTypeEnum::class,
    ];

    public function modelFilter(): string
    {
        return LinkFilter::class;
    }

    public function group(): BelongsTo|Group
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }
}


<?php

namespace App\Models\Catalog\Videos;

use App\Filters\Catalog\Video\GroupFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Catalog\Videos\GroupFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int sort
 * @property bool active
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property Collection|GroupTranslation[] $translations
 *
 * @method static GroupFactory factory(...$options)
 */
class Group extends BaseModel
{
    use HasTranslations;
    use Filterable;
    use HasFactory;
    use ActiveScopeTrait;
    use SetSortAfterCreate;

    public const TABLE = 'catalog_video_groups';
    protected $table = self::TABLE;

    protected $fillable = [
        'sort',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function modelFilter(): string
    {
        return GroupFilter::class;
    }

    public function links(): HasMany
    {
        return $this->HasMany(VideoLink::class, 'group_id', 'id');
    }
}

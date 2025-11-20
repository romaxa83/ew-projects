<?php

namespace App\Models\Musics;

use App\Contracts\Media\HasMedia;
use App\Filters\Musics\MusicFilter;
use App\Models\BaseModel;
use App\Models\Departments\Department;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveTrait;
use App\Traits\Model\Media\InteractsWithMedia;
use Carbon\Carbon;
use Database\Factories\Musics\MusicFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property bool active
 * @property int department_id
 * @property int interval       // sec
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property array unhold_data
 * @property bool has_unhold_data
 *
 * @see self::department()
 * @property-read Department department
 *
 * @method static MusicFactory factory(int $number = null)
 */
class Music extends BaseModel implements HasMedia
{
    use HasFactory;
    use ActiveTrait;
    use Filterable;
    use InteractsWithMedia;

    protected $table = self::TABLE;
    public const TABLE = 'musics';

    public const MEDIA_COLLECTION_NAME = 'records';
    public const MORPH_NAME = 'music';

    protected $fillable = [
        'unhold_data',
        'has_unhold_data'
    ];

    protected $casts = [
        'active' => 'boolean',
        'has_unhold_data' => 'boolean',
        'unhold_data' => 'array',
    ];

    public function modelFilter(): string
    {
        return MusicFilter::class;
    }

    public function getMediaCollectionName(): string
    {
        return self::MEDIA_COLLECTION_NAME;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes(array_merge(
//                $this->mimeAudio()
            ));
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function hasRecord(): bool
    {
        return $this->media->isNotEmpty();
    }

    public function isHoldState(): bool
    {
        return $this->has_unhold_data;
    }
}

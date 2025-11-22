<?php

namespace App\Models\Catalog\Manuals;

use App\Contracts\Media\HasMedia;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\Model\Media\InteractsWithMedia;
use Database\Factories\Catalog\Manuals\ManualFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int manual_group_id
 *
 * @see Manual::group()
 * @property-read ManualGroup group
 *
 * @method static ManualFactory factory(...$parameters)
 */
class Manual extends BaseModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public const TABLE = 'manuals';
    public const MEDIA_COLLECTION_NAME = 'manuals';

    public const MORPH_NAME = 'manual';

    public const MIMES = [
        'application/pdf',
    ];

    public $timestamps = false;

    public function getMediaCollectionName(): string
    {
        return self::MEDIA_COLLECTION_NAME;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes(self::MIMES)
            ->singleFile();
    }

    public function group(): BelongsTo|ManualGroup
    {
        return $this->belongsTo(ManualGroup::class, 'manual_group_id');
    }
}

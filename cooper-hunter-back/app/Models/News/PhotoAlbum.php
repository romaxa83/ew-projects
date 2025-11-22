<?php

namespace App\Models\News;

use App\Contracts\Media\HasMedia;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\Model\Media\InteractsWithMedia;
use Database\Factories\News\PhotoAlbumFactory;
use Illuminate\Support\Carbon;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static PhotoAlbumFactory factory(...$parameters)
 */
class PhotoAlbum extends BaseModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public const TABLE = 'photo_albums';
    public const MORPH_NAME = 'photo_album';
    public const MEDIA_COLLECTION_NAME = 'photo_album';
    public const THUMBNAIL = 'thumbnail';

    protected $table = self::TABLE;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes($this->mimeImage());
    }

    /** @throws InvalidManipulation */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion($this->getOriginalWebpConversionName())
            ->format(Manipulations::FORMAT_WEBP);

        $this->addMediaConversion(self::THUMBNAIL)
            ->width(720)
            ->height(720);
    }
}

<?php

namespace App\Models\About;

use App\Contracts\Media\HasMedia;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\Media\InteractsWithMedia;
use Database\Factories\About\AboutCompanyFactory;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int id
 *
 * @property-read AboutCompanyTranslation translation
 * @property-read AboutCompanyTranslation[] translations
 *
 * @method static AboutCompanyFactory factory(...$parameters)
 */
class AboutCompany extends BaseModel implements HasMedia
{
    use HasFactory;
    use HasTranslations;
    use InteractsWithMedia;

    public const TABLE = 'about_companies';
    public const MORPH_NAME = 'about_company';
    public const MEDIA_COLLECTION_NAME = 'about_company';

    public const MEDIA_SHORT_VIDEO = 'about_company_short_video';
    public const ADDITIONAL_MEDIA_SHORT_VIDEO = 'about_company_additional_short_video';

    public const VIDEO_PREVIEW = 'about_company_video_preview';
    public const ADDITIONAL_VIDEO_PREVIEW = 'about_company_additional_video_preview';

    public $timestamps = false;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes($this->mimeImage());

        $this->addMediaCollection(self::MEDIA_SHORT_VIDEO)
            ->acceptsMimeTypes($this->mimeVideo())
            ->singleFile();

        $this->addMediaCollection(self::ADDITIONAL_MEDIA_SHORT_VIDEO)
            ->acceptsMimeTypes($this->mimeVideo())
            ->singleFile();

        $this->addMediaCollection(self::VIDEO_PREVIEW)
            ->acceptsMimeTypes($this->mimeImage())
            ->singleFile();

        $this->addMediaCollection(self::ADDITIONAL_VIDEO_PREVIEW)
            ->acceptsMimeTypes($this->mimeImage())
            ->singleFile();
    }

    /**
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion($this->getOriginalWebpConversionName())
            ->format(Manipulations::FORMAT_WEBP);

        $this->addMediaConversion(self::VIDEO_PREVIEW)
            ->width(954)
            ->height(541);

        $this->addMediaConversion(self::ADDITIONAL_VIDEO_PREVIEW)
            ->width(954)
            ->height(541);
    }
}

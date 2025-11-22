<?php

namespace App\Models\Warranty\WarrantyInfo;

use App\Contracts\Media\HasMedia;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\Media\InteractsWithMedia;
use Database\Factories\Warranty\WarrantyInfo\WarrantyInfoFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property string video_link
 *
 * @see WarrantyInfo::packages()
 * @property-read Collection|WarrantyInfoPackage[] packages
 *
 * @method static WarrantyInfoFactory factory(...$parameters)
 */
class WarrantyInfo extends BaseModel implements HasMedia
{
    use HasFactory;
    use HasTranslations;
    use InteractsWithMedia;

    public const TABLE = 'warranty_infos';

    public const MEDIA_COLLECTION_NAME = 'warranty_info';

    public const MORPH_NAME = 'warranty_info';

    public const MIMES = [
        'application/pdf',
    ];

    public $timestamps = false;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes(self::MIMES)
            ->singleFile();
    }

    public function packages(): HasMany|WarrantyInfoPackage
    {
        return $this->hasMany(WarrantyInfoPackage::class)
            ->orderBy('sort');
    }
}

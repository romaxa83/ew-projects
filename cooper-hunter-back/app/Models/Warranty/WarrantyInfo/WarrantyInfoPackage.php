<?php

namespace App\Models\Warranty\WarrantyInfo;

use App\Contracts\Media\HasMedia;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Warranty\WarrantyInfo\WarrantyInfoPackageFactory;

/**
 * @property int id
 * @property int warranty_info_id
 * @property int sort
 *
 * @method static WarrantyInfoPackageFactory factory(...$parameters)
 */
class WarrantyInfoPackage extends BaseModel implements HasMedia
{
    use HasFactory;
    use HasTranslations;
    use SetSortAfterCreate;
    use InteractsWithMedia;

    public const TABLE = 'warranty_info_packages';
    public const MEDIA_COLLECTION_NAME = 'warranty_info_package';
    public const MORPH_NAME = 'warranty_info_package';

    public $timestamps = false;

    protected $table = self::TABLE;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->singleFile();
    }
}

<?php

namespace App\Models\Sliders;

use App\Contracts\Media\HasMedia;
use App\Filters\Sliders\SliderFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Sliders\SliderFactory;

/**
 * @property int id
 * @property bool active
 * @property int sort
 *
 * @method static SliderFactory factory(...$parameters)
 */
class Slider extends BaseModel implements HasMedia
{
    use HasFactory;
    use Filterable;
    use InteractsWithMedia;
    use SetSortAfterCreate;
    use HasTranslations;

    public const TABLE = 'sliders';
    public const MORPH_NAME = 'slider';
    public const MEDIA_COLLECTION_NAME = 'slider';

    public const CONVERSIONS = [
        'small' => [
            'width' => 535,
            'height' => 201,
        ],
        'medium' => [
            'width' => 925,
            'height' => 337,
        ],
        'big' => [
            'width' => 1497,
            'height' => 546,
        ],
    ];

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'sort',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->singleFile()
            ->acceptsMimeTypes(
                array_merge(
                    $this->mimeImage(),
                    $this->mimeVideo(),
                )
            );
    }

    public function modelFilter(): string
    {
        return SliderFilter::class;
    }
}

<?php

namespace App\Foundations\Modules\Seo\Models;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Media\Contracts\HasMedia;
use App\Foundations\Modules\Media\Images\SeoImage;
use App\Foundations\Modules\Media\Traits\InteractsWithMedia;
use App\Foundations\Modules\Seo\Factories\SeoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int id
 * @property string model_type
 * @property int model_id
 * @property string|null h1
 * @property string|null title
 * @property string|null desc
 * @property string|null keywords
 * @property string|null text
 *
 * @method static SeoFactory factory(...$parameters)
 */
class Seo extends BaseModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public const TABLE = 'seo';
    protected $table = self::TABLE;

    public $timestamps = false;

    public const MEDIA_COLLECTION_NAME = 'image';
    public const MORPH_NAME = 'seo';

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function getImageClass(): string
    {
        return SeoImage::class;
    }

    protected static function newFactory(): SeoFactory
    {
        return SeoFactory::new();
    }
}

<?php

namespace App\Models\Catalog\Pdf;

use App\Contracts\Media\HasMedia;
use App\Filters\Catalog\PdfFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\Model\Media\InteractsWithMedia;
use Database\Factories\Catalog\Pdf\PdfFactory;

/**
 * @property int id
 * @property string path
 *
 * @method static PdfFactory factory(...$options)
 */
class Pdf extends BaseModel implements HasMedia
{
    use InteractsWithMedia;
    use Filterable;

    public const TABLE = 'pdfs';
    public const MEDIA_COLLECTION_NAME = 'pdfs';

    public const MORPH_NAME = 'pdf';

    public const MIMES = [
        'application/pdf',
    ];

    public $timestamps = false;

    protected $fillable = ['path'];

    public function modelFilter(): string
    {
        return PdfFilter::class;
    }

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
}


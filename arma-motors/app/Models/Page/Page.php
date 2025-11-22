<?php

namespace App\Models\Page;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Media\File;

/**
 * @property int $id
 * @property string $alias
 *
 */
class Page extends BaseModel
{
    public $timestamps = false;

    public const TABLE = 'pages';

    protected $table = self::TABLE;

    // тип для файла
    public const FILE_PDF_TYPE = 'page_pdf';

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class, 'page_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(PageTranslation::class,'page_id', 'id')->where('lang', \App::getLocale());
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'entity');
    }

    public function file(): MorphOne
    {
        return $this->morphOne(File::class, 'entity')
            ->where('type', self::FILE_PDF_TYPE);
    }
}

<?php

namespace App\Models\Content\OurCases;

use App\Contracts\Media\HasMedia;
use App\Filters\Content\OurCaseCategoryFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Content\OurCases\OurCaseCategoryFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property bool active
 * @property int sort
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static OurCaseCategoryFactory factory(...$parameters)
 */
class OurCaseCategory extends BaseModel implements HasMedia
{
    use HasFactory;
    use HasTranslations;
    use Filterable;
    use SetSortAfterCreate;
    use InteractsWithMedia;

    public const TABLE = 'our_case_categories';
    public const MORPH_NAME = 'our_case_category';
    public const MEDIA_COLLECTION_NAME = 'our_case_category';

    protected $fillable = [
        'sort',
        'active',
    ];

    public function modelFilter(): string
    {
        return OurCaseCategoryFilter::class;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes($this->mimeImage())
            ->singleFile();
    }

    public function cases(): HasMany|OurCase
    {
        return $this->hasMany(OurCase::class);
    }
}

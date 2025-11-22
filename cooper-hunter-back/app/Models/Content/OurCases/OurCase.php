<?php

namespace App\Models\Content\OurCases;

use App\Contracts\Media\HasMedia;
use App\Filters\Content\OurCaseFilter;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Content\OurCases\OurCaseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int our_case_category_id
 * @property bool active
 * @property int sort
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static OurCaseFactory factory(...$parameters)
 */
class OurCase extends BaseModel implements HasMedia
{
    use HasFactory;
    use Filterable;
    use SetSortAfterCreate;
    use HasTranslations;
    use InteractsWithMedia;

    public const TABLE = 'our_cases';
    public const MORPH_NAME = 'our_case';
    public const MEDIA_COLLECTION_NAME = 'our_case';

    public const CONVERSIONS = [
        'small' => [
            'width' => 171,
            'height' => 140,
        ],
        'medium' => [
            'width' => 342,
            'height' => 140,
        ],
    ];

    protected $fillable = [
        'our_case_category_id',
        'active',
        'sort',
    ];

    public function modelFilter(): string
    {
        return OurCaseFilter::class;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes($this->mimeImage());
    }

    public function category(): BelongsTo|OurCaseCategory
    {
        return $this->belongsTo(OurCaseCategory::class, 'our_case_category_id');
    }

    public function products(): BelongsToMany|Product
    {
        return $this->belongsToMany(
            Product::class,
            OurCaseProduct::TABLE,
        );
    }
}

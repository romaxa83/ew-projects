<?php

namespace App\Models\Catalog\Troubleshoots;

use App\Contracts\Media\HasMedia;
use App\Filters\Catalog\Troubleshoots\TroubleshootFilter;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Catalog\Troubleshoots\TroubleshootFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int sort
 * @property bool active
 * @property string name
 * @property int group_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see Troubleshoot::products()
 * @property-read Collection|Product[] products
 *
 * @method static TroubleshootFactory factory(...$options)
 */
class Troubleshoot extends BaseModel implements HasMedia
{
    use Filterable;
    use HasFactory;
    use ActiveScopeTrait;
    use InteractsWithMedia;
    use SetSortAfterCreate;

    public const TABLE = 'catalog_troubleshoots';

    public const MEDIA_COLLECTION_NAME = 'troubleshoots';

    public const MORPH_NAME = 'troubleshoot';

    public const MIMES = [
        'application/pdf',
    ];

    protected $table = self::TABLE;

    protected $fillable = [
        'sort',
        'active',
        'group_id',
        'name',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

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

    public function modelFilter(): string
    {
        return TroubleshootFilter::class;
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }
}

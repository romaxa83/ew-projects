<?php

namespace App\Models\Inventories;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Media\Contracts\HasMedia;
use App\Foundations\Modules\Media\Images\CategoryImage;
use App\Foundations\Modules\Media\Traits\InteractsWithMedia;
use App\Foundations\Modules\Seo\Contracts\HasSeo;
use App\Foundations\Modules\Seo\Traits\InteractsWithSeo;
use App\Foundations\Traits\Filters\Filterable;
use App\ModelFilters\Inventories\CategoryFilter;
use Database\Factories\Inventories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Eloquent;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int id
 * @property bool active
 * @property string name
 * @property string slug
 * @property string|null desc
 * @property int position
 * @property int|null origin_id
 * @property int|null parent_id
 * @property bool display_menu
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see self::inventories()
 * @property Inventory[]|HasMany $inventories
 *
 * @see self::children()
 * @property Category[]|HasMany children
 *
 * @see self::allChildren()
 * @property Category[]|HasMany allChildren
 *
 * @mixin Eloquent
 *
 * @method static CategoryFactory factory(...$parameters)
 */
class Category extends BaseModel implements HasSeo, HasMedia
{
    use HasFactory;
    use Filterable;
    use InteractsWithSeo;
    use InteractsWithMedia;

    public const TABLE = 'inventory_categories';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'inventory_category';

    public const IMAGE_HEADER_FIELD_NAME = 'header_image';
    public const IMAGE_MENU_FIELD_NAME = 'menu_image';
    public const IMAGE_MOBILE_FIELD_NAME = 'mobile_image';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'active' => 'boolean',
        'display_menu' => 'boolean'
    ];

    public function getImageClass(): string
    {
        return CategoryImage::class;
    }

    public function modelFilter()
    {
        return $this->provideFilter(CategoryFilter::class);
    }

    public function hasRelatedEntities(): bool
    {
        return $this->inventories()->exists();
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'category_id');
    }

    public function inventoriesOnlyTrashed(): HasMany
    {
        return $this->inventories()->onlyTrashed();
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function hasChildrenRelatedEntities(): bool
    {
        foreach ($this->allChildren as $child) {
            if ($child->inventories()->exists()) {
                return true;
            }
        }

        return false;
    }

    public function getHeaderImg(): null|Media
    {
        return $this->getFirstImage(self::IMAGE_HEADER_FIELD_NAME);
    }

    public function getMenuImg(): null|Media
    {
        return $this->getFirstImage(self::IMAGE_MENU_FIELD_NAME);
    }

    public function getMobileImg(): null|Media
    {
        return $this->getFirstImage(self::IMAGE_MOBILE_FIELD_NAME);
    }
}

<?php

namespace WezomCms\Orders\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\GetForSelectTrait;
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\Model\OrderBySort;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * WezomCms\Orders\Models\DeliveryVariant
 *
 * @property int $id
 * @property int $sort
 * @property bool $published
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Orders\Models\DeliveryVariantTranslation[] $translations
 * @method static Builder|DeliveryVariant listsTranslations($translationField)
 * @method static Builder|DeliveryVariant newModelQuery()
 * @method static Builder|DeliveryVariant newQuery()
 * @method static Builder|DeliveryVariant notTranslatedIn($locale = null)
 * @method static Builder|DeliveryVariant orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|DeliveryVariant orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|DeliveryVariant orderByTranslation($key, $sortmethod = 'asc')
 * @method static Builder|DeliveryVariant published()
 * @method static Builder|DeliveryVariant publishedWithSlug($slug, $slugField = 'slug')
 * @method static Builder|DeliveryVariant query()
 * @method static Builder|DeliveryVariant sorting()
 * @method static Builder|DeliveryVariant translated()
 * @method static Builder|DeliveryVariant translatedIn($locale = null)
 * @method static Builder|DeliveryVariant whereCreatedAt($value)
 * @method static Builder|DeliveryVariant whereId($value)
 * @method static Builder|DeliveryVariant whereImage($value)
 * @method static Builder|DeliveryVariant whereSort($value)
 * @method static Builder|DeliveryVariant wherePublished($value)
 * @method static Builder|DeliveryVariant whereTranslation($key, $value, $locale = null)
 * @method static Builder|DeliveryVariant whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|DeliveryVariant whereUpdatedAt($value)
 * @method static Builder|DeliveryVariant withTranslation()
 * @mixin \Eloquent
 * @mixin DeliveryVariantTranslation
 */
class DeliveryVariant extends Model
{
    use Translatable;
    use GetForSelectTrait;
    use HasFactory;
    use PublishedTrait;
    use ImageAttachable;
    use OrderBySort;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['published', 'sort'];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    protected $translatedAttributes = ['name', 'text'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['published' => 'bool'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translation'];

    /**
     * @return array
     */
    public function imageSettings(): array
    {
        return ['image' => 'cms.orders.delivery-and-payment.delivery_variant.images'];
    }
}

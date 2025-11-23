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
 * WezomCms\Orders\Models\PaymentVariant
 *
 * @property int $id
 * @property int $sort
 * @property bool $published
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Orders\Models\PaymentVariantTranslation[] $translations
 * @method static Builder|PaymentVariant listsTranslations($translationField)
 * @method static Builder|PaymentVariant newModelQuery()
 * @method static Builder|PaymentVariant newQuery()
 * @method static Builder|PaymentVariant notTranslatedIn($locale = null)
 * @method static Builder|PaymentVariant orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|PaymentVariant orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|PaymentVariant orderByTranslation($key, $sortmethod = 'asc')
 * @method static Builder|PaymentVariant published()
 * @method static Builder|PaymentVariant publishedWithSlug($slug, $slugField = 'slug')
 * @method static Builder|PaymentVariant query()
 * @method static Builder|PaymentVariant sorting()
 * @method static Builder|PaymentVariant translated()
 * @method static Builder|PaymentVariant translatedIn($locale = null)
 * @method static Builder|PaymentVariant whereCreatedAt($value)
 * @method static Builder|PaymentVariant whereId($value)
 * @method static Builder|PaymentVariant whereImage($value)
 * @method static Builder|PaymentVariant whereSort($value)
 * @method static Builder|PaymentVariant wherePublished($value)
 * @method static Builder|PaymentVariant whereTranslation($key, $value, $locale = null)
 * @method static Builder|PaymentVariant whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|PaymentVariant whereUpdatedAt($value)
 * @method static Builder|PaymentVariant withTranslation()
 * @mixin \Eloquent
 * @mixin PaymentVariantTranslation
 */
class PaymentVariant extends Model
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
        return ['image' => 'cms.orders.delivery-and-payment.payment_variant.images'];
    }
}

<?php

namespace WezomCms\Orders\Models;

use Cache;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\GetForSelectTrait;
use WezomCms\Core\Traits\Model\OrderBySort;
use WezomCms\Core\Traits\Model\PublishedTrait;
use WezomCms\Orders\Contracts\DeliveryDriverInterface;

/**
 * \WezomCms\Orders\Models\Delivery
 *
 * @property int $id
 * @property int $sort
 * @property bool $published
 * @property string|null $driver
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Payment[] $payments
 * @property-read Collection|DeliveryTranslation[] $translations
 * @method static Builder|Delivery listsTranslations($translationField)
 * @method static Builder|Delivery newModelQuery()
 * @method static Builder|Delivery newQuery()
 * @method static Builder|Delivery notTranslatedIn($locale = null)
 * @method static Builder|Delivery orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|Delivery orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Delivery orderByTranslation($key, $sortmethod = 'asc')
 * @method static Builder|Delivery published()
 * @method static Builder|Delivery publishedWithSlug($slug, $slugField = 'slug')
 * @method static Builder|Delivery query()
 * @method static Builder|Delivery sorting()
 * @method static Builder|Delivery translated()
 * @method static Builder|Delivery translatedIn($locale = null)
 * @method static Builder|Delivery whereCreatedAt($value)
 * @method static Builder|Delivery whereDriver($value)
 * @method static Builder|Delivery whereId($value)
 * @method static Builder|Delivery whereSort($value)
 * @method static Builder|Delivery wherePublished($value)
 * @method static Builder|Delivery whereTranslation($key, $value, $locale = null)
 * @method static Builder|Delivery whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Delivery whereUpdatedAt($value)
 * @method static Builder|Delivery withTranslation()
 * @mixin Eloquent
 * @mixin DeliveryTranslation
 */
class Delivery extends Model
{
    use Translatable;
    use GetForSelectTrait;
    use PublishedTrait;
    use OrderBySort;
    use HasFactory;

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
    protected $translatedAttributes = ['name'];

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

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class);
    }

    /**
     * @param array $arguments
     * @param string $namespace
     * @return DeliveryDriverInterface|null
     */
    public function makeDriver(
        array $arguments = [],
        string $namespace = 'WezomCms\\Orders\\Drivers\\Delivery'
    ): ?DeliveryDriverInterface {
        if (!$this->driver) {
            return null;
        }

        return Cache::driver('array')
            ->rememberForever(
                "delivery-driver-{$this->driver}",
                function () use ($namespace, $arguments) {
                    $fullClassName = (string)Str::of($namespace)
                        ->rtrim('\\')
                        ->append('\\', Str::studly($this->driver));

                    return class_exists($fullClassName) ? app($fullClassName, $arguments) : null;
                }
            );
    }
}

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
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\Model\OrderBySort;
use WezomCms\Core\Traits\Model\PublishedTrait;
use WezomCms\Orders\Contracts\PaymentDriverInterface;

/**
 * \WezomCms\Orders\Models\Payment
 *
 * @property int $id
 * @property int $sort
 * @property bool $published
 * @property string|null $driver
 * @property string|null $icon
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Delivery[] $deliveries
 * @property-read Collection|PaymentTranslation[] $translations
 * @method static Builder|Payment listsTranslations($translationField)
 * @method static Builder|Payment newModelQuery()
 * @method static Builder|Payment newQuery()
 * @method static Builder|Payment notTranslatedIn($locale = null)
 * @method static Builder|Payment orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|Payment orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Payment orderByTranslation($key, $sortmethod = 'asc')
 * @method static Builder|Payment published()
 * @method static Builder|Payment publishedWithSlug($slug, $slugField = 'slug')
 * @method static Builder|Payment query()
 * @method static Builder|Payment sorting()
 * @method static Builder|Payment translated()
 * @method static Builder|Payment translatedIn($locale = null)
 * @method static Builder|Payment whereCreatedAt($value)
 * @method static Builder|Payment whereDriver($value)
 * @method static Builder|Payment whereId($value)
 * @method static Builder|Payment whereSort($value)
 * @method static Builder|Payment wherePublished($value)
 * @method static Builder|Payment whereTranslation($key, $value, $locale = null)
 * @method static Builder|Payment whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Payment whereUpdatedAt($value)
 * @method static Builder|Payment withTranslation()
 * @mixin Eloquent
 * @mixin PaymentTranslation
 */
class Payment extends Model
{
    use Translatable;
    use ImageAttachable;
    use GetForSelectTrait;
    use PublishedTrait;
    use OrderBySort;
    use HasFactory;

    public const TABLE = 'payments';

    protected $table = self::TABLE;

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

    public function imageSettings(): array
    {
        return [
            'icon' => 'cms.orders.delivery-and-payment.payment.icon',
        ];
    }

    public function deliveries(): BelongsToMany
    {
        return $this->belongsToMany(Delivery::class);
    }

    /**
     * @param array $arguments
     * @param string $namespace
     * @return PaymentDriverInterface|null
     */
    public function makeDriver(
        array $arguments = [],
        string $namespace = 'WezomCms\\Orders\\Drivers\\Payment'
    ): ?PaymentDriverInterface {
        if (!$this->driver) {
            return null;
        }

        return Cache::driver('array')
            ->rememberForever(
                "payment-driver-$this->driver",
                function () use ($namespace, $arguments) {
                    $fullClassName = (string)Str::of($namespace)
                        ->rtrim('\\')
                        ->append('\\', Str::studly($this->driver));

                    return class_exists($fullClassName) ? app($fullClassName, $arguments) : null;
                }
            );
    }

    public function validatePayment(array $data = []): bool
    {
        $driver = $this->makeDriver($data);

        return $driver ? $driver->validatePayment() : true;
    }
}

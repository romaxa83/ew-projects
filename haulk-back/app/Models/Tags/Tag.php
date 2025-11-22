<?php

namespace App\Models\Tags;

use App\Collections\Models\Orders\TagsCollection;
use App\ModelFilters\Tags\TagFilter;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\DiffableInterface;
use App\Models\Orders\Order;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Scopes\CompanyScope;
use App\Traits\Diffable;
use App\Traits\SetCompanyId;
use Database\Factories\Tags\TagFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\Tags\Tag
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $color
 * @property int|null broker_id
 * @property int|null carrier_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @see Tag::orders()
 * @property Order[]|MorphToMany orders
 *
 * @see Tag::trucks()
 * @property Truck[]|MorphToMany trucks
 *
 * @see Tag::trailers()
 * @property Trailer[]|MorphToMany trailers
 *
 * @see Tag::vehicleOwners()
 * @property VehicleOwner[]|MorphToMany vehicleOwners
 *
 * @mixin Eloquent
 *
 * @method static TagFactory factory(...$parameters)
 */
class Tag extends Model implements DiffableInterface
{
    use Filterable;
    use SetCompanyId;
    use Diffable;
    use HasFactory;

    public const TABLE_NAME = 'tags';

    public const TYPE_ORDER = 'order';
    public const TYPE_TRUCKS_AND_TRAILER = 'trucks_and_trailer';
    public const TYPE_VEHICLE_OWNER = 'vehicle_owner';

    public const TYPES = [
        self::TYPE_ORDER,
        self::TYPE_TRUCKS_AND_TRAILER,
        self::TYPE_VEHICLE_OWNER,
    ];

    public const TYPES_BS = [
        self::TYPE_TRUCKS_AND_TRAILER,
        self::TYPE_VEHICLE_OWNER,
    ];

    public const TYPES_CRM = [
        self::TYPE_ORDER,
        self::TYPE_TRUCKS_AND_TRAILER,
        self::TYPE_VEHICLE_OWNER,
    ];

    public const MAX_TAGS_COUNT_PER_TYPE = 10;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'color',
        'type',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(function($model) {
            $model->setCompanyId();
        });
    }

    public function modelFilter(): string
    {
        return TagFilter::class;
    }

    public function orders(): MorphToMany
    {
        return $this->morphedByMany(Order::class, 'taggable');
    }

    public function trucks(): MorphToMany
    {
        return $this->morphedByMany(Truck::class, 'taggable');
    }

    public function trailers(): MorphToMany
    {
        return $this->morphedByMany(Trailer::class, 'taggable');
    }

    public function customers(): MorphToMany
    {
        return $this->morphedByMany(VehicleOwner::class, 'taggable');
    }

    public function vehicleOwners(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'taggable');
    }

    public function hasRelatedEntities(): bool
    {
        if ($this->type === Tag::TYPE_ORDER) {
            return $this->orders()->exists();
        }

        if ($this->type === Tag::TYPE_TRUCKS_AND_TRAILER) {
            return $this->trucks()->exists() || $this->trailers()->exists();
        }

        if ($this->type === Tag::TYPE_VEHICLE_OWNER) {
            return $this->vehicleOwners()->exists() || $this->customers()->exists();
        }

        return false;
    }

    public function newCollection(array $models = []): TagsCollection
    {
        return TagsCollection::make($models);
    }
}

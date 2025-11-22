<?php

namespace App\Models\Tags;

use App\Collections\Tag\TagCollection;
use App\Enums\Tags\TagType;
use App\Foundations\Models\BaseModel;
use App\ModelFilters\Tags\TagFilter;
use App\Models\Customers\Customer;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Database\Factories\Tags\TagFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property int id
 * @property string name
 * @property TagType type
 * @property string color
 * @property string|null origin_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see self::trucks()
 * @property Truck[]|MorphToMany trucks
 *
 * @see self::trailers()
 * @property Trailer[]|MorphToMany trailers
 *
 * @see self::customers()
 * @property Customer[]|MorphToMany customers
 *
 * @mixin Eloquent
 *
 * @method static TagFactory factory(...$parameters)
 */
class Tag extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'tags';
    protected $table = self::TABLE;

    public const MAX_TAGS_COUNT_PER_TYPE = 10;
    public const ECOM_NAME_TAG = 'Ecomm';

    /** @var array<string, string> */
    protected $casts = [
        'type' => TagType::class,
    ];

    public function modelFilter(): string
    {
        return TagFilter::class;
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
        return $this->morphedByMany(Customer::class, 'taggable');
    }

    public function hasRelatedEntities(): bool
    {
        if ($this->type->isTrucksAndTrailer()) {
            return $this->trucks()->exists() || $this->trailers()->exists();
        }

        if ($this->type->isCustomer()) {
            return $this->customers()->exists();
        }

        return false;
    }

    public function newCollection(array $models = []): TagCollection
    {
        return TagCollection::make($models);
    }
}

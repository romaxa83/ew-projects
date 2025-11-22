<?php

namespace App\Models\Catalog\Favourites;

use App\Contracts\Favourites\Favorable;
use App\Contracts\Members\Member;
use App\Events\Favourites\FavouriteCreatedEvent;
use App\Events\Favourites\FavouriteDeletedEvent;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\Catalog\Favourites\FavouriteFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string favorable_type
 * @property int favorable_id
 * @property string member_type
 * @property int member_id
 * @property Carbon created_at
 *
 * @see Favourite::member()
 * @property-read Member member
 *
 * @see Favourite::favorable()
 * @property-read Favorable favorable
 *
 * @method static FavouriteFactory factory(...$parameters)
 */
class Favourite extends BaseModel
{
    use HasFactory;

    public const TABLE = 'favourites';
    public const UPDATED_AT = null;

    protected $table = self::TABLE;

    protected $fillable = [
        'member_type',
        'member_id',
        'favorable_type',
        'favorable_id',
        'created_at',
    ];

    protected $dispatchesEvents = [
        'created' => FavouriteCreatedEvent::class,
        'deleted' => FavouriteDeletedEvent::class,
    ];

    public function member(): MorphTo
    {
        return $this->morphTo('member');
    }

    public function favorable(): MorphTo
    {
        return $this->morphTo('favorable');
    }
}

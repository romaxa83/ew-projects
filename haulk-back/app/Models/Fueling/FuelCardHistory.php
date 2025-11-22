<?php

namespace App\Models\Fueling;

use App\Collections\Models\Users\CommentsCollection;
use App\Models\DiffableInterface;
use App\Models\Users\User;
use App\Traits\Diffable;
use App\Traits\SetCompanyId;
use Database\Factories\Fueling\FuelCardHistoryFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int fuel_card_id
 * @property int user_id
 * @property boolean active
 * @property Carbon created_at
 * @property Carbon|null updated_at
 * @property Carbon date_assigned
 * @property Carbon|null date_unassigned
 *
 * @see self::user()
 * @property User user
 *
 * @see self::fuelCard()
 * @property FuelCard fuelCard
 *
 * @mixin Eloquent
 * @method static FuelCardHistoryFactory factory(...$parameters)
 */
class FuelCardHistory extends Model
{
    use HasFactory;

    public const TABLE_NAME = 'fuel_card_histories';

    public $timestamps = false;

    protected $fillable = [
        'fuel_card_id',
        'user_id',
        'active',
        'date_assigned',
        'date_unassigned',
    ];

    protected $casts = [
        'date_assigned' => 'datetime',
        'date_unassigned' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function fuelCard(): BelongsTo
    {
        return $this->belongsTo(FuelCard::class, 'fuel_card_id', 'id');
    }
}

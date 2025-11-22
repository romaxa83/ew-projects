<?php

namespace App\Models\BodyShop\VehicleOwners;

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $vehicle_owner_id
 * @property string $comment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @see OrderComment::user()
 * @property User user
 */
class VehicleOwnerComment extends Model
{
   public const TABLE_NAME = 'bs_vehicle_owner_comments';

   protected $table = self::TABLE_NAME;

    protected $fillable = [
        'comment',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        /** @var BelongsTo|User $belongsTo */
        $belongsTo = $this->belongsTo(User::class, 'user_id', 'id');

        return $belongsTo->withTrashed();
    }
}

<?php

namespace App\Models\BodyShop\Orders;

use App\Collections\Models\BodyShop\Orders\CommentsCollection;
use App\Models\DiffableInterface;
use App\Models\Users\User;
use App\Traits\Diffable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property string comment
 *
 * @property int user_id
 * @property int order_id
 *
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see OrderComment::user()
 * @property User user
 *
 *  * @see OrderComment::order()
 * @property Order order
 */
class OrderComment extends Model implements DiffableInterface
{
    use Diffable;

    public const TABLE_NAME = 'bs_order_comments';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'comment',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function user(): BelongsTo
    {
        /** @var BelongsTo|User $belongsTo */
        $belongsTo = $this->belongsTo(User::class, 'user_id', 'id');

        return $belongsTo->withTrashed();
    }

    public function newCollection(array $models = []): CommentsCollection
    {
        return CommentsCollection::make($models);
    }
}

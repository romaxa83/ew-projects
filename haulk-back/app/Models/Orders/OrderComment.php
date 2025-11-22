<?php

namespace App\Models\Orders;

use App\Collections\Models\Orders\CommentsCollection;
use App\Models\DiffableInterface;
use App\Models\Users\User;
use App\Scopes\CompanyScope;
use App\Traits\Diffable;
use App\Traits\SetCompanyId;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string comment
 * @property string timezone
 *
 * @property int user_id
 * @property int order_id
 * @property int role_id
 *
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see OrderComment::user()
 * @property User user
 *
 * @mixin Eloquent
 */
class OrderComment extends Model implements DiffableInterface
{
    use Diffable;
    use SetCompanyId;
    use HasFactory;

    public const TABLE_NAME = 'order_comments';

    protected $fillable = [
        'comment',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(function($model) {
            $model->setCompanyId();
        });
    }

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

<?php

namespace App\Models\Vehicles\Comments;

use App\Collections\Models\Vehicles\CommentsCollection;
use App\Models\DiffableInterface;
use App\Models\Users\User;
use App\Traits\Diffable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $comment
 * @property string $timezone,
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @see OrderComment::user()
 * @property User user
 *
 * @see Order::scopeServiceContext(int $isBodyShop)
 * @method static Builder|Comment serviceContext(int $isBodyShop)
 *
 * @mixin Eloquent
 */
abstract class Comment extends Model implements DiffableInterface
{
    use Diffable;

    protected $fillable = [
        'comment',
        'user_id',
        'timezone',
    ];

    public function user(): BelongsTo
    {
        /** @var BelongsTo|User $belongsTo */
        $belongsTo = $this->belongsTo(User::class, 'user_id', 'id');

        return $belongsTo->withTrashed();
    }

    public function scopeServiceContext(Builder $builder, bool $isBodyShop = false): Builder
    {
        $roles = User::COMPANY_ROLES;

        if ($isBodyShop) {
            $roles = User::BS_ROLES;
        }

        return $builder->whereHas('user', function (Builder $q) use ($roles) {
           $q->whereHas('roles', function(Builder $q) use ($roles) {
               $q->whereIn('name', $roles);
           });
        });
    }

    public function newCollection(array $models = []): CommentsCollection
    {
        return CommentsCollection::make($models);
    }
}

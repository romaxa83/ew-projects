<?php

namespace App\Models\Users;

use App\Collections\Models\Users\CommentsCollection;
use App\Models\DiffableInterface;
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
 * @property int author_id
 *
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see OrderComment::user()
 * @property User user
 *
 * @see OrderComment::author()
 * @property User author
 *
 * @mixin Eloquent
 */
class UserComment extends Model implements DiffableInterface
{
    use SetCompanyId;
    use HasFactory;
    use Diffable;

    public const TABLE_NAME = 'user_comments';

    protected $fillable = [
        'comment',
        'author_id',
        'timezone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function author(): BelongsTo
    {
        /** @var BelongsTo|User $belongsTo */
        $belongsTo = $this->belongsTo(User::class, 'author_id', 'id');

        return $belongsTo->withTrashed();
    }

    public function newCollection(array $models = []): CommentsCollection
    {
        return CommentsCollection::make($models);
    }
}

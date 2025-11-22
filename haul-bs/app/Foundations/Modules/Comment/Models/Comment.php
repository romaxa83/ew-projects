<?php

namespace App\Foundations\Modules\Comment\Models;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Comment\Factories\CommentFactory;
use App\Foundations\Traits\Filters\Filterable;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int id
 * @property string model_type
 * @property int model_id
 * @property int author_id
 * @property string text
 * @property string|null timezone
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see self::author()
 * @property-read User|BelongsTo author
 *
 * @method static CommentFactory factory(...$parameters)
 */
class Comment extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'comments';
    protected $table = self::TABLE;

    public function author(): BelongsTo
    {
        /** @var BelongsTo|User $belongsTo */
        $belongsTo = $this->belongsTo(User::class, 'author_id', 'id');

        return $belongsTo->withTrashed();
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }
}

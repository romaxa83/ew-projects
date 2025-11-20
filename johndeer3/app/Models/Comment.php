<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Comment
 *
 * @property int $id
 * @property string $model
 * @property string $entity_type
 * @property int $entity_id
 * @property string $text
 * @property int $author_id
 * @property boolean $status
 * @property int|null $parent_id
 * @property string $created_at
 * @property string $updated_at
 */

class Comment extends Model
{
    const COMMENT_BY_REPORT = 'comment_by_report';

    protected $table = 'comments';

    public function entity()
    {
        return $this->morphTo();
    }
}
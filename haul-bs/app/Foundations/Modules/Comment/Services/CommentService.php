<?php

namespace App\Foundations\Modules\Comment\Services;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Comment\Models\Comment;
use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;

class CommentService
{
    public function create(
        BaseModel $model,
        User|Authenticatable $author,
        string $text
    ): Comment
    {
        $comment = new Comment();
        $comment->author_id = $author->id;
        $comment->model_id = $model->id;
        $comment->model_type = defined($model::class . '::MORPH_NAME')
            ? $model::MORPH_NAME
            : $model::class;
        $comment->text = $text;

        $comment->save();

        return $comment;
    }

    public function delete(Comment $model): bool
    {
        return $model->delete();
    }
}

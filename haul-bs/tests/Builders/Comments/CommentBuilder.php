<?php

namespace Tests\Builders\Comments;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Comment\Models\Comment;
use App\Models\Users\User;
use Tests\Builders\BaseBuilder;

class CommentBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Comment::class;
    }

    public function author(User $model): self
    {
        $this->data['author_id'] = $model->id;
        return $this;
    }

    public function model(BaseModel $model): self
    {
        $this->data['model_id'] = $model->id;
        $this->data['model_type'] = $model::MORPH_NAME;
        return $this;
    }
}

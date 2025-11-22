<?php

namespace App\Foundations\Modules\History\Strategies\Details\Comment;

use App\Foundations\Modules\Comment\Models\Comment;
use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;

class DeleteStrategy extends BaseDetailsStrategy
{
    public function __construct(protected Comment $model)
    {}

    public function getDetails(): array
    {
        $tmp["comments.{$this->model->id}.comment"] = [
            'old' => $this->model->text,
            'new' => null,
            'type' => self::TYPE_REMOVED
        ];

        return $tmp;
    }
}

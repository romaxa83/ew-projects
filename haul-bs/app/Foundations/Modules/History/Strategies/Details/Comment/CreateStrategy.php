<?php

namespace App\Foundations\Modules\History\Strategies\Details\Comment;

use App\Foundations\Modules\Comment\Models\Comment;
use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;

class CreateStrategy extends BaseDetailsStrategy
{
    public function __construct(protected Comment $model)
    {}

    public function getDetails(): array
    {
        $tmp["comments.{$this->model->id}.comment"] = [
            'old' => null,
            'new' => $this->model->text,
            'type' => self::TYPE_ADDED
        ];

        return $tmp;
    }
}

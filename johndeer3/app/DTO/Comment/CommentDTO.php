<?php

namespace App\DTO\Comment;

use App\Models\Comment;
use App\Models\Report\Report;

class CommentDTO
{
    public $authorID;
    public $comment;

    public $model;
    public $entityId;
    public $entityClass;

    private function __construct()
    {}

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->comment = $data['comment'];
        $self->authorID = $args['author_id'] ?? null;

        return $self;
    }

    public function forReport(Report $model): self
    {
        $this->model = Comment::COMMENT_BY_REPORT;
        $this->entityId = $model->id;
        $this->entityClass = Report::class;

        return $this;
    }
}


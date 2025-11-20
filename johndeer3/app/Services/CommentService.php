<?php

namespace App\Services;

use App\DTO\Comment\CommentDTO;
use App\Models\Comment;
use App\Models\Report\Report;

class CommentService
{
    public function createOrUpdateByReport(Report $report, CommentDTO $dto): Comment
    {
        if($report->comment){
            return $this->update($report->comment, $dto);
        }

        return $this->create($dto->forReport($report));
    }

    public function create(CommentDTO $dto): Comment
    {
        $model = new Comment();
        $model->model = $dto->model;
        $model->entity_type = $dto->entityClass;
        $model->entity_id = $dto->entityId;
        $model->text = $dto->comment;
        $model->author_id = $dto->authorID;

        $model->save();

        return $model;
    }

    public function update(Comment $model, CommentDTO $dto): Comment
    {
        $model->text = $dto->comment;
        $model->author_id = $dto->authorID ?? $model->author_id;

        $model->save();

        return $model;
    }

    public function deleteByReport(Report $report)
    {
        if($report->comment){
            $report->comment->forceDelete();
        }
    }
}

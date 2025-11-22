<?php

namespace App\Services\Support;

use App\DTO\Support\MessageDTO;
use App\Exceptions\ErrorsCode;
use App\Models\Support\Message;

class MessageService
{
    public function __construct()
    {}

    public function create(MessageDTO $dto): Message
    {
        try {
            $model = new Message();
            $model->category_id = $dto->getCategoryId();
            $model->email = $dto->getEmail();
            $model->text = $dto->getText();
            $model->user_id = $dto->getUserId();

            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function changeStatus(Message $model, int $status): Message
    {
        try {
            Message::assertStatus($status);

            $model->status = $status;
            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function delete(Message $model ): void
    {
        try {
            if(!$model->isDone()){
                throw new \DomainException(__('error.can\'t delete support message'), ErrorsCode::BAD_REQUEST);
            }

            $model->forceDelete();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}


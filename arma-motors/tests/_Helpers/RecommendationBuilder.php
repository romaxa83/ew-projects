<?php

namespace Tests\_Helpers;

use App\Models\Recommendation\Recommendation;

class RecommendationBuilder
{
    private $uuid = '9ee4670a-0016-11ec-8274-4cd98fc26f15';
    private $userId;
    private $carUuid = '9ee4670a-0016-11ec-8274-4cd98fc26f13';
    private $orderUuid = '9ee4670a-0016-11ec-8274-4cd98fc26f11';
    private $qty = 3;
    private $text = 'text';
    private $comment = 'comment';
    private $rejectionReason = 'reject reason';
    private $author = 'author';
    private $executor = 'executor';
    private $completed = false;
    private $completionAt = null;
    private $relevanceAt = null;
    private $status = Recommendation::STATUS_NEW;

    public function setCarUuid(string $value): self
    {
        $this->carUuid = $value;

        return $this;
    }

    public function setOrderUuid(string $value): self
    {
        $this->orderUuid = $value;

        return $this;
    }

    public function setStatus(string $value): self
    {
        $this->status = $value;

        return $this;
    }

    public function setUserId(string $value): self
    {
        $this->userId = $value;

        return $this;
    }

    public function create()
    {
        $model = $this->save();

        return $model;
    }

    private function save()
    {
        $data = [
            'uuid' => $this->uuid,
            'car_uuid' => $this->carUuid,
            'order_uuid' => $this->orderUuid,
            'qty' => $this->qty,
            'text' => $this->text,
            'comment' => $this->comment,
            'rejection_reason' => $this->rejectionReason,
            'author' => $this->author,
            'executor' => $this->executor,
            'completed' => $this->completed,
            'completion_at' => $this->completionAt,
            'relevance_at' => $this->relevanceAt,
            'status' => $this->status,
        ];

        if($this->userId){
            $data["user_id"] = $this->userId;
        }

        return Recommendation::factory()->new($data)->create();
    }
}


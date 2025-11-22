<?php

namespace App\Services\Order;

use App\Models\Recommendation\Recommendation;
use App\Repositories\Order\RecommendationRepository;
use App\Repositories\User\CarRepository;
use App\Services\BaseService;

class RecommendationService extends BaseService
{
    public function __construct(
        protected CarRepository $carRepository,
        protected RecommendationRepository $repo,
    )
    {}

    public function create(array $data): Recommendation
    {
        $model = new Recommendation();
        $model->uuid = $data["uuid"];
        $model->user_id = $data["userId"];
        $model->car_uuid = $data["auto"];
        $model->order_uuid = $data["request"] ?? null;
        $model->qty = $data["quantity"] ?? null;
        $model->text = $data["recommendation"] ?? null;
        $model->author = $data["author"] ?? null;
        $model->executor = $data["executor"] ?? null;
        $model->rejection_reason = $data["rejectionReason"] ?? null;
        $model->comment = $data["comment"] ?? null;
        $model->completion_at = $data["dateCompletion"] ?? null;
        $model->relevance_at = $data["dateRelevance"] ?? null;
        $model->completed = $data["completed"] ?? null;
        $model->data = json_encode($data);

        $model->save();

        return $model;
    }

    public function edit(Recommendation $model, array $data): void
    {
        $model->completed = $data["completed"];
        if($model->completed && !$model->isUsed()){
            $model = $this->setOldStatus($model, false);
        }
        $model->order_uuid = $data["request"] ?? null;
        $model->qty = $data["quantity"] ?? null;
        $model->text = $data["recommendation"] ?? null;
        $model->author = $data["author"] ?? null;
        $model->executor = $data["executor"] ?? null;
        $model->rejection_reason = $data["rejectionReason"] ?? null;
        $model->comment = $data["comment"] ?? null;
        $model->completion_at = $data["dateCompletion"] ?? null;
        if($model->completion_at && !$model->isUsed()){
            $model = $this->setOldStatus($model, false);
        }
        $model->relevance_at = $data["dateRelevance"] ?? null;

        $model->data = json_encode($data);

        $model->save();
    }

    public function setUsedStatus(Recommendation $model, bool $save = true): void
    {
        $model->status = Recommendation::STATUS_USED;

        if($save){
            $model->save();
        }
    }

    public function setUsedStatusFromID($id): void
    {
        $this->setUsedStatus($this->repo->getByID($id));
    }

    public function setOldStatus(Recommendation $model, bool $save = true): Recommendation
    {
        $model->status = Recommendation::STATUS_OLD;

        if($save){
            $model->save();
        }

        return $model;
    }
}



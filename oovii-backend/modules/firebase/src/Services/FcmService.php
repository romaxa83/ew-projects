<?php

namespace WezomCms\Firebase\Services;

use WezomCms\Firebase\Dto\FcmDto;
use WezomCms\Firebase\Models\FcmNotification;

class FcmService
{
    public function create(FcmDto $dto): FcmNotification
    {
        $model = new FcmNotification();
        $model->user_id = $dto->user_id;
        $model->entity_type = $dto->entity_type;
        $model->entity_id = $dto->entity_id;
        $model->status = $dto->status;
        $model->type = $dto->type;
        $model->send_data = $dto->send_data;

        $model->save();

        return $model;
    }
}


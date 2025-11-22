<?php

namespace App\Services\AA;

use App\Models\AA\AAResponse;
use Illuminate\Database\Eloquent\Model;

class ResponseService
{
    public function __construct()
    {}

    public function save(
        array $data,
        string $url,
        null|string $type = null,
        null|Model $model = null,
        string $status = AAResponse::STATUS_SUCCESS
    ): AAResponse
    {
        $obj = new AAResponse();
        $obj->url = $url;
        $obj->message = $data['message'] ?? null;
//        $obj->data = $data['data'] ?? [];
        $obj->data = array_to_json($data);
        $obj->status = $data['success'] ?? false;
        $obj->type = $type;
        $obj->status = $status;

        if($model){
            $obj->entity_type = $model::class;
            $obj->entity_id = $model->id;
        }

        $obj->save();

        return $obj;
    }

    public function setError(
        AAResponse $model,
        string $status,
        string $message
    ): AAResponse
    {
        $model->status = $status;
        $model->message = $message;

        $model->save();

        return $model;
    }
}



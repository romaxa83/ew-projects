<?php

namespace App\Http\Resources\FileBrowser;

use Illuminate\Http\Resources\Json\JsonResource;

class FileActionErrorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'success' => false,
            'data' => [
                'code' => 422,
                'messages' => $this->resource,
            ],
        ];
    }
}

<?php

namespace App\Http\Resources\FileBrowser;

use Illuminate\Http\Resources\Json\JsonResource;

class SuccessActionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'success' => true,
            'time' => now(),
            'data' => [
                'code' => 220,
            ],
        ];
    }
}

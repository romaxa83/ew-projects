<?php

namespace App\Http\Resources\FileBrowser;

use Illuminate\Http\Resources\Json\JsonResource;

class FolderCreatedResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'success' => true,
            'time' => now(),
            'code' => 220,
            'data' => [
                'messages' => [
                    __('Directory created successfully'),
                ],
            ],
        ];
    }
}

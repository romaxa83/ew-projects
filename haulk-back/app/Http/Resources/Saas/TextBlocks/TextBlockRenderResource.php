<?php

namespace App\Http\Resources\Saas\TextBlocks;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class TextBlockRenderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     *
     * @OA\Schema (
     *     schema="TextBlockRender",
     *     type="object",
     *     @OA\Property (
     *          property="data",
     *          type="object",
     *          description="Text block"
     *     )
     * )
     */
    public function toArray($request): array
    {
        $list = $this->resource['list'];
        $language = $this->resource['language'];
        $result = [];

        foreach ($list as $item) {
            $result[$item['group'] . '_' . $item['block']] = !empty($item[$language]) ? $item[$language] : $item['en'];
        }

        return $result;
    }
}

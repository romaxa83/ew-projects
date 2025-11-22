<?php

namespace App\Http\Resources\Saas\Support;

use App\Models\Saas\Support\SupportRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabelsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema (
     *     schema="SupportRequestLabels",
     *     @OA\Property (
     *          property="data",
     *          type="array",
     *          @OA\Items (
     *              allOf={
     *                  @OA\Schema (
     *                      @OA\Property (property="id", type="integer", description="Label Id"),
     *                      @OA\Property (property="name", type="string", description="Label name")
     *                  )
     *              }
     *          )
     *     )
     * )
     */
    public function toArray($request): array
    {
        $response = [];
        foreach ($this['labels'] as $id => $name) {
            if ($id === SupportRequest::LABEL_OTHER) {
                continue;
            }
            $response[] = [
                'id' => $id,
                'name' => $name
            ];
        }
        $response[] = [
            'id'=> SupportRequest::LABEL_OTHER,
            'name' => SupportRequest::LABELS_DESCRIPTION[SupportRequest::LABEL_OTHER]
        ];
        return $response;
    }
}

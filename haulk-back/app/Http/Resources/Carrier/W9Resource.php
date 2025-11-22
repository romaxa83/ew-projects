<?php


namespace App\Http\Resources\Carrier;

use App\Http\Resources\Files\FileResource;
use App\Models\Saas\Company\Company;
use Illuminate\Http\Resources\Json\JsonResource;

class W9Resource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="CarrierW9Resource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Carrier w9 image link",
     *            allOf={
     *                @OA\Schema(
     *                    required={"w9_form_image"},
     *                        @OA\Property(property="w9_form_image", type="object", description="Carrier w9", allOf={
     *                              @OA\Schema(ref="#/components/schemas/File")
     *                        }),
     *                )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            Company::W9_FIELD_CARRIER => FileResource::make(
                $this->getFirstMedia(Company::W9_FIELD_CARRIER)
            ),
        ];
    }

}

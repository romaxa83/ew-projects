<?php


namespace App\Http\Resources\Translates;


use App\Models\Translates\Translate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranslateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *   schema="TranslateList",
     *   @OA\Property(
     *      property="data",
     *      description="Translate list",
     *      type="object",
     *   ),
     * )
     */
    public function toArray($request)
    {
        return $this->resource;
    }
}
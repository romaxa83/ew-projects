<?php

namespace App\Http\Resources\Localizations;

use App\Foundations\Modules\Localization\Models\Translation;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TranslationResourceRaw",
 *     type="object",
 *     allOf={@OA\Schema(
 *          required={"key", "place", "lang"},
 *          @OA\Property(property="key", type="string", example="button.create"),
 *          @OA\Property(property="place", type="string", example="site"),
 *          @OA\Property(property="lang", type="string", example="en"),
 *          @OA\Property(property="text", type="string", example="create"),
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="TranslationResourceList",
 *     @OA\Property(property="data", description="Language list", type="array",
 *         @OA\Items(ref="#/components/schemas/TranslationResourceRaw")
 *     ),
 *  )
 *

 */
class TranslationResource extends JsonResource
{
    public function toArray($request): array
    {
        return $this->resource;
    }
}

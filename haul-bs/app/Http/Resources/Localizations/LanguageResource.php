<?php

namespace App\Http\Resources\Localizations;

use App\Foundations\Modules\Localization\Models\Language;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="LanguageResourceRaw",
 *     type="object",
 *     allOf={@OA\Schema(
 *          required={"name", "native", "slug"},
 *          @OA\Property(property="native", type="integer", example="Español"),
 *          @OA\Property(property="name", type="string", example="Spanish"),
 *          @OA\Property(property="slug", type="string", example="es"),
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="LanguageResourceList",
 *     @OA\Property(property="data", description="Language list", type="array",
 *     @OA\Items(ref="#/components/schemas/LanguageResourceRaw")
 *     ),
 * )
 *
 * @mixin Language
 */
class LanguageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'native' => $this->native,
            'slug' => $this->slug,
        ];
    }
}

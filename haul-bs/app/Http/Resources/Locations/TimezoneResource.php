<?php

namespace App\Http\Resources\Locations;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TimezoneRaw", type="object",
 *         allOf={@OA\Schema(
 *             @OA\Property(property="timezone", type="string", description="Timezone", example="America/Puerto_Rico"),
 *             @OA\Property(property="title", type="string", description="Timezone title", example="(USA) UTC -04:00 Atlantic Standard Time, AST"),
 *             @OA\Property(property="country_code", type="string", example="us"),
 *             @OA\Property(property="country_name", type="string", example="USA"),
 *            )
 *      }
 * )
 *
 * @OA\Schema(
 *     schema="TimezoneResource",
 *     @OA\Property(property="data", description="Timezone list", type="array",
 *     @OA\Items(ref="#/components/schemas/TimezoneRaw")
 *     ),
 * )
 *
 */

class TimezoneResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'timezone' => $this['timezone'],
            'title' => $this['title'],
            'country_code' => $this['country_code'],
            'country_name' => $this['country_name'],
        ];
    }
}

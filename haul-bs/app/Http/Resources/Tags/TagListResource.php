<?php

namespace App\Http\Resources\Tags;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="TagRaw", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "name", "color", "type", "hasRelatedEntities"},
 *         @OA\Property(property="id", type="integer", description="Tag id", example=1),
 *         @OA\Property(property="name", type="string", description="Tag title", example="Empty"),
 *         @OA\Property(property="color", type="string", description="Tag color", example="#2F54EB"),
 *         @OA\Property(property="type", type="string", enum={"trucks_and_trailer", "customer"}, description="Tag type"),
 *         @OA\Property(property="hasRelatedEntities", type="boolean", description="Tag has related entities"),
 *         @OA\Property(property="hasRelatedTrucks", type="boolean", description="Tag has related trucks"),
 *         @OA\Property(property="hasRelatedTrailers", type="boolean", description="Tag has related trailers"),
 *     )}
 * )
 *
 * @OA\Schema(schema="TagTypeRaw",
 *     @OA\Property(property="tag_type", description="Tags list by type", type="array",
 *         @OA\Items(ref="#/components/schemas/TagRaw")
 *     ),
 * )
 *
 * @OA\Schema(schema="TagListResource", type="object",
 *     @OA\Property(property="data", type="object", description="Tags type", allOf={
 *         @OA\Schema(
 *             @OA\Property(property="tag_type", description="Tags list by type", type="array",
 *                 @OA\Items(ref="#/components/schemas/TagRaw")
 *             ),
 *         )}
 *     ),
 * )
 */

class TagListResource extends JsonResource
{
    protected bool $preserveKeys = true;
}


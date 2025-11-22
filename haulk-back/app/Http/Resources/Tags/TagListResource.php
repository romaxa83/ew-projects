<?php

namespace App\Http\Resources\Tags;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagListResource extends JsonResource
{
    protected bool $preserveKeys = true;
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="TagRaw",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                    required={"id", "name", "color", "type", "hasRelatedEntities"},
     *                    @OA\Property(property="id", type="integer", description="Tag id"),
     *                    @OA\Property(property="name", type="string", description="Tag title"),
     *                    @OA\Property(property="color", type="string", description="Tag color"),
     *                    @OA\Property(property="type", type="string", enum={"order"}, description="Tag type"),
     *                    @OA\Property(property="hasRelatedEntities", type="boolean", description="Tag has related entities"),
     *                    @OA\Property(property="hasRelatedTrucks", type="boolean", description="Tag has related trucks"),
     *                    @OA\Property(property="hasRelatedTrailers", type="boolean", description="Tag has related trailers"),
     *                )
     *        }
     * )
     *
     * @OA\Schema(
     *    schema="TagTypeRaw",
     *    @OA\Property(
     *        property="tag_type",
     *        description="Tags list by type",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/TagRaw")
     *    ),
     * )
     *
     * @OA\Schema(
     *   schema="TagList",
     *   type="object",
     *   @OA\Property(
     *       property="data",
     *       type="object",
     *       description="Tags type",
     *       allOf={
     *           @OA\Schema(
     *               @OA\Property(
     *                   property="tag_type",
     *                   description="Tags list by type",
     *                   type="array",
     *                   @OA\Items(ref="#/components/schemas/TagRaw")
     *               ),
     *           )
     *        }
     *    ),
     * )
     */
}

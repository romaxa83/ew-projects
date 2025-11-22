<?php

namespace App\Http\Resources\Saas\TextBlocks;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class TextBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="TextBlocksList",
     *     type="object",
     *      @OA\Property (
     *          property="data",
     *          type="array",
     *          description="Text block list",
     *          @OA\Items (ref="#/components/schemas/TextBlockObject")
     *      ),
     *      @OA\Property(
     *         property="links",
     *         ref="#/components/schemas/PaginationLinks",
     *      ),
     *      @OA\Property(
     *         property="meta",
     *         ref="#/components/schemas/PaginationMeta",
     *      ),
     * )
     *
     * @OA\Schema (
     *     schema="TextBlock",
     *     type="object",
     *     @OA\Property (
     *          property="data",
     *          type="object",
     *          description="Text block",
     *          allOf={
     *              @OA\Schema (ref="#/components/schemas/TextBlockObject")
     *          }
     *     )
     * )
     *
     * @OA\Schema (
     *     schema="TextBlockObject",
     *     type="object",
     *     @OA\Property (property="id", type="integer", description="Text block id"),
     *     @OA\Property (property="block", type="string", description="Block name in page"),
     *     @OA\Property (property="group", type="string", description=""),
     *     @OA\Property (property="scope", type="array", description="", @OA\Items (type="string")),
     *     @OA\Property (property="en", type="string", description="English text for block"),
     *     @OA\Property (property="es", type="string", description="Spanish text for block"),
     *     @OA\Property (property="ru", type="string", description="Russian text for block"),
     *     @OA\Property (property="created_at", type="created_at", description="Create date/time"),
     *     @OA\Property (property="updated_at", type="updated_at", description="Update date/time")
     *
     * )
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}

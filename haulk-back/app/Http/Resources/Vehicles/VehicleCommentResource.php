<?php

namespace App\Http\Resources\Vehicles;

use App\Http\Resources\Files\ImageResource;
use App\Models\Vehicles\Comments\Comment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Comment
 */
class VehicleCommentResource extends JsonResource
{
    /**
     * @OA\Schema(
     *    schema="VehicleCommentResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Comment id"),
     *            @OA\Property(property="comment", type="string", description="Comment text"),
     *            @OA\Property(property="author", type="object", description="Comment author",
     *                @OA\Schema(type="object", allOf={
     *                    @OA\Schema(
     *                        @OA\Property(property="id", type="integer", description="Comment author id"),
     *                        @OA\Property(property="full_name", type="string", description="Comment author name"),
     *                        @OA\Property(property="role_id", type="string", description="Comment author role_id"),
     *                        @OA\Property(property="photo", type="object", description="image with different size",
     *                            allOf={
     *                                @OA\Schema(ref="#/components/schemas/Image")
     *                            }
     *                        ),
     *                    )
     *                }),
     *             ),
     *             @OA\Property(property="timestamp", type="integer", description="Comment timestamp"),
     *             @OA\Property(property="tiezome", type="string", description="Comment timezone"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *    schema="VehicleCommentListResource",
     *    @OA\Property(
     *        property="data",
     *        description="Vehicle comments list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/VehicleCommentResource")
     *    ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'author' => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
                'role_id' => $this->user->roles->first()->id,
                $this->user->getImageField() => ImageResource::make($this->user->getFirstImage()),
            ],
            'timestamp' => $this->created_at->timestamp,
            'timezone' => $this->timezone,
        ];
    }
}

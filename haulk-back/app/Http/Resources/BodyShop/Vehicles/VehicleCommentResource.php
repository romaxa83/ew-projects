<?php

namespace App\Http\Resources\BodyShop\Vehicles;

use App\Models\BodyShop\Settings\Settings;
use App\Models\Vehicles\Comments\Comment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Comment
 */
class VehicleCommentResource extends JsonResource
{
    /**
     * @OA\Schema(
     *    schema="VehicleCommentBSResource",
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
     *    schema="VehicleCommentBSListResource",
     *    @OA\Property(
     *        property="data",
     *        description="Vehicle comments list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/VehicleCommentBSResource")
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
            ],
            'timestamp' => $this->created_at->timestamp,
            'timezone' => Settings::getParam('timezone'),
        ];
    }
}

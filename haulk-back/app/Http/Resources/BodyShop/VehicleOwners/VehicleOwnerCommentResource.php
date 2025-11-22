<?php

namespace App\Http\Resources\BodyShop\VehicleOwners;

use App\Models\BodyShop\Settings\Settings;
use App\Models\Vehicles\Comments\Comment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Comment
 */
class VehicleOwnerCommentResource extends JsonResource
{
    /**
     * @OA\Schema(
     *    schema="VehicleOwnerCommentAuthorResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Comment author id"),
     *            @OA\Property(property="full_name", type="string", description="Comment author name"),
     *            @OA\Property(property="role_id", type="string", description="Comment author role_id"),
     *        )
     *     }
     * )
     *
     * @OA\Schema(
     *    schema="VehicleOwnerCommentResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Comment id"),
     *            @OA\Property(property="comment", type="string", description="Comment text"),
     *            @OA\Property(property="author", type="object", ref="#/components/schemas/VehicleOwnerCommentAuthorResource"),
     *             @OA\Property(property="timestamp", type="integer", description="Comment timestamp"),
     *             @OA\Property(property="tiezome", type="string", description="Comment timezone"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *    schema="VehicleOwnerCommentListResource",
     *    @OA\Property(
     *        property="data",
     *        description="Vehicle comments list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/VehicleOwnerCommentResource")
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

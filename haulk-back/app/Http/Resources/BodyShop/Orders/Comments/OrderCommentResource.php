<?php

namespace App\Http\Resources\BodyShop\Orders\Comments;

use App\Models\BodyShop\Orders\OrderComment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderComment
 */
class OrderCommentResource extends JsonResource
{
    /**
     * @OA\Schema(
     *    schema="OrderCommentBSResource",
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
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *    schema="OrderCommentBSListResource",
     *    @OA\Property(
     *        property="data",
     *        description="Order comments list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/OrderCommentBSResource")
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
        ];
    }
}

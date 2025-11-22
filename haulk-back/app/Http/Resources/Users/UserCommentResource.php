<?php

namespace App\Http\Resources\Users;

use App\Models\Users\UserComment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserComment
 */
class UserCommentResource extends JsonResource
{
    /**
     * @OA\Schema(
     *    schema="UserCommentAuthorResource",
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
     *    schema="UserCommentResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Comment id"),
     *            @OA\Property(property="comment", type="string", description="Comment text"),
     *            @OA\Property(property="author", type="object", ref="#/components/schemas/UserCommentAuthorResource"),
     *            @OA\Property(property="timestamp", type="integer", description="Comment timestamp"),
     *            @OA\Property(property="tiezome", type="string", description="Comment timezone"),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *    schema="UserCommentListResource",
     *    @OA\Property(
     *        property="data",
     *        description="User comments list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/UserCommentResource")
     *    ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'author' => [
                'id' => $this->author->id,
                'full_name' => $this->author->full_name,
                'role_id' => $this->author->roles->first()->id,
            ],
            'timestamp' => $this->created_at->timestamp,
            'timezone' => $this->timezone,
        ];
    }
}

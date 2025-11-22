<?php

namespace App\Http\Resources\Common;

use App\Foundations\Modules\Comment\Models\Comment;
use App\Http\Resources\Permissions\RoleResource;
use App\Models\Settings\Settings;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="CommentAuthorResource", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "full_name", "role"},
 *         @OA\Property(property="id", type="integer", description="Comment author id", example=1),
 *         @OA\Property(property="full_name", type="string", description="Comment author name", example="John Doe"),
 *         @OA\Property(property="role", type="object", ref="#/components/schemas/RoleResource"),
 *     )}
 * )
 *
 * @OA\Schema(schema="CommentResource", type="object", allOf={
 *     @OA\Schema(
 *         required={"id", "comment", "author", "timestamp"},
 *         @OA\Property(property="id", type="integer", description="Comment id", example=1),
 *         @OA\Property(property="comment", type="string", description="Comment text", example="some comment"),
 *         @OA\Property(property="author", type="object", ref="#/components/schemas/CommentAuthorResource"),
 *         @OA\Property(property="timestamp", type="integer", description="Comment timestamp", example="1690362347"),
 *         @OA\Property(property="timezone", type="string", description="Comment timezone", example="America/Chicago"),
 *     )}
 * )
 *
 * @OA\Schema(schema="CommentListResource",
 *     @OA\Property(property="data", description="Comments list", type="array",
 *         @OA\Items(ref="#/components/schemas/CommentResource")
 *     ),
 * )
 *
 * @mixin Comment
 */
class CommentResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'comment' => $this->text,
            'author' => [
                'id' => $this->author->id,
                'full_name' => $this->author->full_name,
                'role' => RoleResource::make($this->author->role),
            ],
            'timestamp' => $this->created_at->timestamp,
            'timezone' => Settings::getParam('timezone'),
        ];
    }
}


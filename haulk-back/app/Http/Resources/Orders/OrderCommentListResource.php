<?php

namespace App\Http\Resources\Orders;

use App\Http\Resources\Files\ImageResource;
use App\Models\Orders\OrderComment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderComment
 */
class OrderCommentListResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @OA\Schema(
     *    schema="OrderCommentListResource",
     *    @OA\Property(
     *        property="data",
     *        description="Order comments list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/OrderCommentResourceRaw")
     *    ),
     * )
     *
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'author' => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
                'role_id' => $this->role_id,
                $this->user->getImageField() => ImageResource::make($this->user->getFirstImage()),
            ],
            'timestamp' => $this->created_at->timestamp,
            'timezone' => $this->timezone,
        ];
    }
}

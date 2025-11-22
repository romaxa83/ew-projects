<?php

namespace App\Http\Resources\Orders;

use App\Http\Resources\Files\ImageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="OrderCommentResourceRaw",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                        @OA\Property(property="id", type="integer", description="Comment id"),
     *                        @OA\Property(property="comment", type="string", description="Comment text"),
     *                        @OA\Property(property="author", type="object", description="Comment author", @OA\Schema(
     *                        type="object",
     *                        allOf={
     *                            @OA\Schema(
     *                                @OA\Property(property="id", type="integer", description="Comment author id"),
     *                                @OA\Property(
     *                                    property="full_name", type="string", description="Comment author name"
     *                                ),
     *                                @OA\Property(
     *                                    property="photo", type="object", description="image with different size",
     *                                    allOf={
     *                                        @OA\Schema(ref="#/components/schemas/Image")
     *                                    }
     *                                ),
     *                            )
     *                        }
     *                        ),),
     *                    @OA\Property(property="timestamp", type="integer", description="Comment timestamp"),
     *                    @OA\Property(property="timezone", type="integer", description="Comment timezone"),
     *                )
     *        }
     * )
     *
     * @OA\Schema(
     *    schema="OrderCommentResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Order comment data",
     *            allOf={
     *                @OA\Schema(
     *                        @OA\Property(property="id", type="integer", description="Comment id"),
     *                        @OA\Property(property="comment", type="string", description="Comment text"),
     *                        @OA\Property(property="author", type="object", description="Comment author", @OA\Schema(
     *                        type="object",
     *                        allOf={
     *                            @OA\Schema(
     *                                @OA\Property(property="id", type="integer", description="Comment author id"),
     *                                @OA\Property(
     *                                    property="full_name", type="string", description="Comment author name"
     *                                ),
     *                                @OA\Property(
     *                                    property="photo", type="object", description="image with different size",
     *                                    allOf={
     *                                        @OA\Schema(ref="#/components/schemas/Image")
     *                                    }
     *                                ),
     *                            )
     *                        }
     *                        ),),
     *                        @OA\Property(property="timestamp", type="integer", description="Comment timestamp"),
     *                        @OA\Property(property="timezone", type="integer", description="Comment timezone"),
     *                )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'author' => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
                $this->user->getImageField() => ImageResource::make($this->user->getFirstImage()),
            ],
            'timestamp' => $this->created_at->timestamp,
            'timezone' => $this->timezone,
        ];
    }
}

<?php

namespace App\Http\Resources\Saas\Support;

use App\Http\Resources\Files\FileResource;
use App\Http\Resources\Files\ImageResource;
use App\Models\Saas\Support\SupportRequestMessage;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class SupportMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema (
     *     schema="SupportMessagePaginatedResource",
     *    @OA\Property(
     *        property="data",
     *        type="array",
     *        @OA\Items (ref="#/components/schemas/SupportMessageRawResource")
     *    ),
     *    @OA\Property(
     *        property="links",
     *        ref="#/components/schemas/PaginationLinks",
     *    ),
     *    @OA\Property(
     *        property="meta",
     *        ref="#/components/schemas/PaginationMeta",
     *    ),
     * )
     *
     * @OA\Schema (
     *     schema="SupportRequestMessageResource",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        ref="#/components/schemas/SupportMessageRawResource"
     *    )
     * )
     *
     * @OA\Schema (
     *     schema="SupportRequestAuthorResource",
     *     type="object",
     *    @OA\Property (property="id", type="integer", nullable=true,),
     *    @OA\Property (property="full_name", type="string",),
     *    @OA\Property (property="email", type="string",),
     *    @OA\Property (property="phone", type="string",),
     *    @OA\Property (property="role", type="object", nullable=true, allOf={
     *        @OA\Schema (
     *            @OA\Property (property="id", type="integer",),
     *            @OA\Property (property="name", type="string",),
     *        )
     *    }),
     *    @OA\Property (property="is_support_employee", type="boolean",),
     *    @OA\Property(property="photo", type="object", nullable=true, ref="#/components/schemas/Image")
     * )
     *
     * @OA\Schema (
     *     schema="SupportMessageRawResource",
     *     type="object",
     *     allOf={
     *         @OA\Schema (
     *             @OA\Property (property="id", type="integer",),
     *             @OA\Property (property="message", type="string"),
     *             @OA\Property (property="created_at", type="number"),
     *             @OA\Property (property="author", type="object", nullable=true, ref="#/components/schemas/SupportRequestAuthorResource"),
     *             @OA\Property (property="is_my_message", type="boolean",),
     *             @OA\Property (property="attachments", nullable=true, type="array", @OA\Items(ref="#/components/schemas/FileRaw"))
     *         )
     *     }
     * )
     */
    public function toArray($request): array
    {
        /**@var SupportRequestMessage $message*/
        $message = $this;

        $response = [
            'id' => $message->id,
            'message' => $message->message,
            'created_at' => Carbon::parse($message->created_at)->timestamp,
            'author' => $message->getAuthorData(),
            'is_my_message' => $request->user() && $message->isMyMessage($request->user()),
        ];

        $files = $message->getMedia(SupportRequestMessage::MEDIA_COLLECTION);

        if ($files) {
            $response['attachments'] = FileResource::collection($files->all());
        }

        return $response;
    }
}

<?php

namespace App\Http\Resources\Files;

use App\Foundations\Modules\Media\Models\File;
use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="FilePaginate",
 *   @OA\Property(property="data", description="File paginated list", type="array",
 *      @OA\Items(ref="#/components/schemas/File")
 *   ),
 *   @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *   @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 * )
 *
 * @OA\Schema(schema="File", type="object",
 *     @OA\Property(property="data", type="object", description="File data", allOf={
 *          @OA\Schema(required={"id", "full_name", "email", "phone","status","security_level"},
 *              @OA\Property(property="id", type="integer", description="File id"),
 *              @OA\Property(property="url", type="string", description="File url"),
 *              @OA\Property(property="name", type="string", description="File name"),
 *              @OA\Property(property="file_name", type="string", description="Full file name"),
 *              @OA\Property(property="mime_type", type="string", description="Mime type"),
 *              @OA\Property(property="size", type="integer", description="File size"),
 *              @OA\Property(property="created_at", type="integer", description="Creation timestamp"),
 *          )
 *       }
 *    ),
 * ),
 * @OA\Schema(schema="FileRaw",
 *       @OA\Property(property="id", type="integer", description="File id"),
 *       @OA\Property(property="url", type="string", description="File url"),
 *       @OA\Property(property="name", type="string", description="File name"),
 *       @OA\Property(property="file_name", type="string", description="Full file name"),
 *       @OA\Property(property="mime_type", type="string", description="Mime type"),
 *       @OA\Property(property="size", type="integer", description="File size"),
 *       @OA\Property(property="created_at", type="integer", description="Creation timestamp"),
 *    )
 * )
 *
 * @mixin File
 */
class FileResource extends JsonResource
{
    use TransformFullUrl;

    public function toArray($request): array
    {
        /** @var $file File */
        $file = $this;

        return [
            'id' => $file->id,
            'name' => $file->name,
            'file_name' => $file->file_name,
            'mime_type' => $file->mime_type,
            'url' => $this->fullUrl($file->resource),
            'size' => $file->size,
            'created_at' => $file->created_at->timestamp,
        ];
    }
}


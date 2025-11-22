<?php

namespace App\Http\Resources\Library;

use App\Models\Library\LibraryDocument;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * @property mixed id
 * @property mixed name
 * @property mixed created_at
 * @property mixed owner
 * @property mixed user
 */
class LibraryDocumentResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     *
     * @OA\Schema(
     *   schema="LibraryDocumentModel",
     *   type="object",
     *      @OA\Property(
     *          property="data",
     *          type="object",
     *              description="Library model data",
     *              allOf={
     *                  @OA\Schema(
     *                      required={"id", "url", "file_name","policy","isOwner","created_at"},
     *                      @OA\Property(property="id", type="integer", description="Library model id"),
     *                      @OA\Property(property="QuestionAnswer", type="string", description="Library model path"),
     *                      @OA\Property(property="file_name", type="string", description="Library model name"),
     *                      @OA\Property(property="policy", type="string", description="Library model policy"),
     *                      @OA\Property(property="isOwner", type="boolean", description="Library model isOwner"),
     *                      @OA\Property(property="created_at", type="integer", description="Library model created_at"),
     *                  )
     *           }
     *      ),
     * )
     *
     * @OA\Schema(
     *   schema="LibraryDocumentResource",
     *   type="object",
     *      allOf={
     *          @OA\Schema(
     *              required={"id", "url", "file_name","policy","isOwner","created_at"},
     *              @OA\Property(property="id", type="integer", description="Library model id"),
     *              @OA\Property(property="url", type="string", description="Library model  url"),
     *              @OA\Property(property="file_name", type="string", description="Library model file_name"),
     *              @OA\Property(property="mime_type", type="string", description="Library model mime_type"),
     *              @OA\Property(property="size", type="integer", description="Library size of documents in bytes"),
     *              @OA\Property(property="policy", type="string", description="Library model policy"),
     *              @OA\Property(property="who", type="string", description="Who uploaded the document"),
     *              @OA\Property(property="whom", type="string", description="For whom the document was uploaded"),
     *              @OA\Property(property="isOwner", type="boolean", description="Library model isOwner"),
     *              @OA\Property(property="created_at", type="integer", description="Library model created_at"),
     *          )
     *      }
     * )
     *
     */

    public function toArray($request)
    {
        $libraryDocument = $this;
        return [
            'id' => $libraryDocument->id,
            'url' => $libraryDocument->getFirstMedia(LibraryDocument::MEDIA_COLLECTION_NAME)->getFullUrl(),
            'file_name' => $libraryDocument->getFirstMedia(LibraryDocument::MEDIA_COLLECTION_NAME)->file_name,
            'mime_type' => $libraryDocument->getFirstMedia(LibraryDocument::MEDIA_COLLECTION_NAME)->mime_type,
            'size' => $libraryDocument->getFirstMedia(LibraryDocument::MEDIA_COLLECTION_NAME)->size,
            'policy' => $libraryDocument->getPolicy(),
            'who' => $libraryDocument->owner
                        ? $libraryDocument->owner->getRoleName() . ' - ' . $libraryDocument->owner->full_name
                        : '',
            'whom' => $libraryDocument->getWhom(),
            'isOwner' => $libraryDocument->isDownloadedByTheUser(Auth::user()->id),
            'created_at' => $libraryDocument->created_at->timestamp,
        ];
    }
}

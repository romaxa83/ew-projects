<?php

namespace App\Http\Resources\Library;

use App\Models\Library\LibraryDocument;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class LibraryDocumentPaginatedResource extends ResourceCollection
{
    public $collects = 'App\Http\Resources\Library\LibraryDocumentResource';

    /**
     * Transform the resource collection into an array.
     *
     * @OA\Schema(
     *   schema="LibraryDocumentPaginatedResource",
     *   @OA\Property(
     *      property="data",
     *      description="Library Document model paginated list",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/LibraryDocumentResource")
     *   ),
     *   @OA\Property(
     *      property="links",
     *      ref="#/components/schemas/PaginationLinks",
     *   ),
     *   @OA\Property(
     *      property="meta",
     *      ref="#/components/schemas/PaginationMeta",
     *   ),
     * )
     * @param $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}

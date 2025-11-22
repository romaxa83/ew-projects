<?php

namespace App\Http\Resources\QuestionAnswer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class QuestionAnswerPaginatedResource extends ResourceCollection
{
    public $collects = 'App\Http\Resources\QuestionAnswer\QuestionAnswerResource';
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     *
     *   @OA\Schema(
     *   schema="QuestionAnswerPaginatedResource",
     *   @OA\Property(
     *      property="data",
     *      description="Question Answer model paginated list",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/QuestionAnswerResource")
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
     */
}

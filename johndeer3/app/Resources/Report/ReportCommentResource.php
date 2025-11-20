<?php

namespace App\Resources\Report;

use App\Helpers\DateFormat;
use App\Models\Comment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="ReportComment Resource",
 *     @OA\Property(property="comment", type="string", description="Комментарий", example="отчет готов"),
 *     @OA\Property(property="created", type="string", description="Дата создания комментария", example="22.06.2020 10:04"),
 * )
 */

class ReportCommentResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Comment $comment */
        $comment = $this;

        return [
            'comment' => $comment->text,
            'created' => DateFormat::front($comment->updated_at),
        ];
    }
}

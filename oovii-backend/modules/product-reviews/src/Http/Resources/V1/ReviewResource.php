<?php

namespace WezomCms\ProductReviews\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\ProductReviews\Models\ProductReview;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Product review Resource",
 *     description="Product review Resource",
 * )
 */
class ReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model ProductReview */
        $model = $this;

        return [
            'id' => $model->id,
            'text' => $model->text,
            'like' => $model->parent_id ? null : $model->like,
            'userName' => $this->userName($model),
            'rating' => $model->rating,
            'createdAt' => $model->created_at->format(config('cms.core.time.format.created_at.api')),
            'answers' => self::collection($model->publishedChildren),
        ];
    }

    private function userName($model): ?string
    {
        return $model->user->full_name ?? $model->name;
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID", example=1),
     * @OA\Property(property="text", title="Text", example="some review"),
     * @OA\Property(property="like", title="Like", description="Лайк - true, дизлайк - false", example=true)
     * @OA\Property(property="userName", title="User name", example="Иван Иванов"),
     * @OA\Property(property="rating", title="Rating", example=4),
     * @OA\Property(property="createdAt", title="Created at", description="Дата создания отзыва", example="2022-01-27")
     * @OA\Property(property="answers", title="Answers", description="Ответы на отзыв", type="array",
     *     @OA\Items(ref="#/components/schemas/ReviewResource"))
     * )
     */
}


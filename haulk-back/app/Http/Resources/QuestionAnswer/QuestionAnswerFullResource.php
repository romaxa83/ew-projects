<?php

namespace App\Http\Resources\QuestionAnswer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionAnswerFullResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="QuestionAnswerFullResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="QuestionAnswer data",
     *            allOf={
     *                @OA\Schema(
     *                    required={"question_en", "question_es", "question_ru", "answer_en", "answer_es", "answer_ru"},
     *                    @OA\Property(property="id", type="integer", description="QuestionAnswer id"),
     *                    @OA\Property(property="question_en", type="string", description="QuestionAnswer title_en"),
     *                    @OA\Property(property="question_es", type="string", description="QuestionAnswer title_ru"),
     *                    @OA\Property(property="question_ru", type="string", description="QuestionAnswer title_es"),
     *                    @OA\Property(property="answer_en", type="text", description="QuestionAnswer body_short_en"),
     *                    @OA\Property(property="answer_es", type="text", description="QuestionAnswer body_short_ru"),
     *                    @OA\Property(property="answer_ru", type="text", description="QuestionAnswer body_short_es"),
     *                )
     *            }
     *        ),
     * )
     *
     */

    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'question_en' => $this->resource->question_en,
            'answer_en' => $this->resource->answer_en,
            'question_es' => $this->resource->question_es,
            'answer_es' => $this->resource->answer_es,
            'question_ru' => $this->resource->question_ru,
            'answer_ru' => $this->resource->answer_ru,
        ];
    }
}

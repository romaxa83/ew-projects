<?php

namespace App\Http\Resources\QuestionAnswer;

use App\Models\Language;
use Illuminate\Http\Request;
use Config;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionAnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *   @OA\Schema(
     *   schema="QuestionAnswerModel",
     *   type="object",
     *      @OA\Property(
     *          property="data",
     *          type="object",
     *              description="QuestionAnswer model data",
     *              allOf={
     *                  @OA\Schema(
     *                      required={"id", "question_en", "answer_es", "question_ru", "answer_en", "answer_es", "answer_ru"},
     *                      @OA\Property(property="id", type="integer", description="QuestionAnswer model id"),
     *                      @OA\Property(property="question", type="string", description="QuestionAnswer model question"),
     *                      @OA\Property(property="answer", type="string", description="QuestionAnswer model answer"),
     *                  )
     *           }
     *      ),
     * )
     *
     * @OA\Schema(
     *   schema="QuestionAnswerResource",
     *   type="object",
     *      allOf={
     *          @OA\Schema(
     *              required={"id", "question", "answer"},
     *              @OA\Property(property="id", type="integer", description="QuestionAnswer model id"),
     *              @OA\Property(property="question", type="string", description="QuestionAnswer model  question"),
     *              @OA\Property(property="answer", type="string", description="QuestionAnswer model answer"),
     *          )
     *      }
     * )
     */
    public function toArray($request)
    {
        $lang = Config::get('app.locale') ? Config::get('app.locale') : 'en';
        return [
            'id' => $this->resource->id,
            'question' => $this->resource->getQuestion($lang),
            'answer' => $this->resource->getAnswer($lang),
        ];
    }
}

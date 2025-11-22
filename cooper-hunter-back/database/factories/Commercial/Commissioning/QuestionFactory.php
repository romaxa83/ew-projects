<?php

namespace Database\Factories\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\AnswerPhotoType;
use App\Enums\Commercial\Commissioning\AnswerType;
use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\Models\Commercial\Commissioning\Protocol;
use App\Models\Commercial\Commissioning\Question;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Question[]|Question create(array $attributes = [])
 */
class QuestionFactory extends BaseFactory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'answer_type' => AnswerType::TEXT,
            'photo_type' => AnswerPhotoType::REQUIRED,
            'protocol_id' => Protocol::factory(),
            'status' => QuestionStatus::DRAFT,
            'sort' => 1
        ];
    }
}

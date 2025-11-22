<?php

namespace Database\Factories\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\ProtocolType;
use App\Models\Commercial\Commissioning\Answer;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Answer[]|Answer create(array $attributes = [])
 */
class AnswerFactory extends BaseFactory
{
    protected $model = Answer::class;

    public function definition(): array
    {
        return [
            'project_protocol_question_id' => ProtocolType::COMMISSIONING,
        ];
    }
}


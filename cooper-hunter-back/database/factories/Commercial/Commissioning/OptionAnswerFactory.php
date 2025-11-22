<?php

namespace Database\Factories\Commercial\Commissioning;

use App\Models\Commercial\Commissioning\OptionAnswer;
use App\Models\Faq\Question;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|OptionAnswer[]|OptionAnswer create(array $attributes = [])
 */
class OptionAnswerFactory extends BaseFactory
{
    protected $model = OptionAnswer::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
        ];
    }
}

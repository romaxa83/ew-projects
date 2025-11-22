<?php

namespace Database\Factories\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use App\Models\Commercial\Commissioning\Question;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|ProjectProtocolQuestion[]|ProjectProtocolQuestion create(array $attributes = [])
 */
class ProjectProtocolQuestionFactory extends BaseFactory
{
    protected $model = ProjectProtocolQuestion::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'project_protocol_id' => ProjectProtocol::factory(),
            'answer_status' => AnswerStatus::NONE(),
            'sort' => 1,
        ];
    }
}




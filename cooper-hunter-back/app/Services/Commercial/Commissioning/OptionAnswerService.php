<?php

namespace App\Services\Commercial\Commissioning;

use App\Dto\Commercial\Commissioning\OptionAnswerDto;
use App\Dto\SimpleTranslationDto;
use App\Models\Commercial\Commissioning\OptionAnswer;
use App\Models\Commercial\Commissioning\OptionAnswerTranslation;
use App\Models\Commercial\Commissioning\Question;
use App\Services\BaseService;
use Core\Exceptions\TranslatedException;

class OptionAnswerService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function create(OptionAnswerDto $dto, Question $question): OptionAnswer
    {
        if($question->isAnswerText()){
            throw new TranslatedException(__('exceptions.commercial.commissioning.not create option answer by question'), 502);
        }
        if(!$question->status->isDraft()){
            throw new TranslatedException(__('exceptions.commercial.commissioning.can\'t create an option answer'), 502);
        }

        $model = new OptionAnswer();
        $model->question_id = $dto->questionId;
        $model->save();

        foreach ($dto->getTranslations() as $translation) {
            /** @var $translation SimpleTranslationDto */
            $t = new OptionAnswerTranslation();
            $t->text = $translation->getText();
            $t->language = $translation->getLanguage();
            $t->row_id = $model->id;
            $t->save();
        }

        return $model;
    }

    public function update(OptionAnswer $model, OptionAnswerDto $dto): OptionAnswer
    {
        if(!$model->question->status->isDraft()){
            throw new TranslatedException(__('exceptions.commercial.commissioning.can\'t update an option answer'), 502);
        }

        foreach ($dto->getTranslations() as $translation) {
            /** @var $translation SimpleTranslationDto */
            /** @var $t OptionAnswerTranslation */
            $t = $model->translations()->where('language', $translation->getLanguage())->first();
            $t->text = $translation->getText();
            $t->save();
        }

        return $model;
    }

    public function delete(OptionAnswer $model): bool
    {
        if(!$model->question->status->isDraft()){
            throw new TranslatedException(__('exceptions.commercial.commissioning.can\'t delete an option answer'), 502);
        }

        return $model->delete();
    }
}


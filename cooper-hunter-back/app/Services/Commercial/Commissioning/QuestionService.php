<?php

namespace App\Services\Commercial\Commissioning;

use App\Dto\Commercial\Commissioning\QuestionDto;
use App\Dto\SimpleTranslationDto;
use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\Models\Commercial\Commissioning\Question;
use App\Models\Commercial\Commissioning\QuestionTranslation;
use App\Services\BaseService;
use Core\Exceptions\TranslatedException;

class QuestionService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function create(QuestionDto $dto): Question
    {
        $model = new Question();
        $model->answer_type = $dto->answerType;
        $model->photo_type = $dto->photoType;
        $model->protocol_id = $dto->protocolId;

        $model = $this->setStatus($model, $dto->status, false);

        $model->save();

        foreach ($dto->getTranslations() as $translation) {
            /** @var $translation SimpleTranslationDto */
            $t = new QuestionTranslation();
            $t->text = $translation->getText();
            $t->language = $translation->getLanguage();
            $t->row_id = $model->id;
            $t->save();
        }

        return $model;
    }

    public function update(Question $model, QuestionDto $dto): Question
    {
        if($model->status->isDraft() && $dto->status->isDraft()){
            $model->answer_type = $dto->answerType;
            $model->photo_type = $dto->photoType;
            $model->save();

            foreach ($dto->getTranslations() as $translation) {
                /** @var $translation SimpleTranslationDto */
                /** @var $t QuestionTranslation */
                $t = $model->translations()->where('language', $translation->getLanguage())->first();
                $t->text = $translation->getText();
                $t->save();
            }

            return $model;
        }

        $model = $this->setStatus($model, $dto->status);

        return $model;
    }

    public function setStatus(Question $model, QuestionStatus $status, bool $save = true): Question
    {
        if($model->status === null){
            $model->status = $status::DRAFT();
            if($save) $model->save();
            return $model;
        }

        if(
            ($model->status->isDraft() && $status->isInactive()) ||
            ($model->status->isActive() && $status->isDraft()) ||
            ($model->status->isInactive() && ($status->isDraft() || $status->isActive()))
        ){
            throw new TranslatedException(__('exceptions.commercial.commissioning.can\'t toggle status',[
                'from_status' => $model->status,
                'to_status' => $status,
            ]), 502);
        }

        $model->status = $status;

        if($save) $model->save();

        return $model;
    }

    public function delete(Question $model): bool
    {
        if(!$model->status->isDraft()){
            throw new TranslatedException(__('exceptions.commercial.commissioning.can\'t delete question'), 502);
        }

        return $model->delete();
    }
}

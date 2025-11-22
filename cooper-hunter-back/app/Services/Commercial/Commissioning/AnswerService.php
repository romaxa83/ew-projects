<?php

namespace App\Services\Commercial\Commissioning;

use App\Dto\Commercial\Commissioning\AnswerDto;
use App\Dto\Commercial\Commissioning\AnswersDto;
use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\Exceptions\Commercial\Commissioning\ValidateException;
use App\Models\Commercial\Commissioning\Answer;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use App\Repositories\Commercial\Commissioning\ProjectProtocolQuestionRepository;
use App\Services\BaseService;

class AnswerService extends BaseService
{
    public $key;

    public function __construct(protected ProjectProtocolQuestionRepository $questionRepository)
    {
        parent::__construct();
    }

    public function createOrUpdate(AnswersDto $dto, ProjectProtocol $projectProtocol): void
    {
        foreach ($dto->getAnswerDtos() as $key => $answerDto){
            $this->key = $key;
            /** @var $answerDto AnswerDto */
            /** @var $projectQuestion ProjectProtocolQuestion */
            $projectQuestion = $projectProtocol->projectQuestions->where('id' , $answerDto->projectProtocolQuestionID)->first();

            if($projectQuestion->answerIsNone()){
                $this->create($answerDto, $projectQuestion);
                $projectQuestion->answer_status = AnswerStatus::DRAFT;
            } elseif ($projectQuestion->answerIsDraft() || $projectQuestion->answerIsReject()) {
                $this->update($answerDto, $projectQuestion);
            }

            $projectQuestion->save();
        }

        if($projectProtocol->isDraft()){
            $projectProtocol->status = ProtocolStatus::PENDING;
            $projectProtocol->save();
        }

        if($projectProtocol->isIssue() && $projectProtocol->total_wrong_answers == 0){
            $projectProtocol->status = ProtocolStatus::PENDING;
            $projectProtocol->save();
        }
    }

    public function create(AnswerDto $dto, ProjectProtocolQuestion $question): Answer
    {
        $this->allCheck($dto, $question);

        $model = new Answer();
        $model->project_protocol_question_id = $dto->projectProtocolQuestionID;
        if($question->question->isAnswerText()){
            $model->text = $dto->text;
        }

        $model->save();

        if(!$question->question->isAnswerText()){
            $model->optionAnswers()->attach($dto->optionAnswerIds);
        }

        if($question->question->photoIsRequired() || $question->question->photoIsNotNecessary()){
            foreach ($dto->media as $image) {
                $model->addMedia($image)
                    ->toMediaCollection($model->getMediaCollectionName());
            }
        }

        return $model;
    }

    public function update(AnswerDto $dto, ProjectProtocolQuestion $question): Answer
    {
        $this->allCheck($dto, $question);

        if($question->question->isAnswerText()){
            $question->answer->text = $dto->text;
        }
        if($question->answerIsReject()){
            $question->answer_status = AnswerStatus::DRAFT;
        }

        $question->answer->save();

        if(!$question->question->isAnswerText()){
            $question->answer->optionAnswers()->detach();
            $question->answer->optionAnswers()->attach($dto->optionAnswerIds);
        }

        return $question->answer;
    }

    public function allCheck(AnswerDto $dto, ProjectProtocolQuestion $question): void
    {
        $this->checkAnswerType($dto, $question);
        $this->checkPhotoType($dto, $question);

        if(!$question->question->isAnswerText()){
            $this->checkApplyOptionAnswers($dto, $question);
            $this->checkRadioType($dto, $question);
        }
    }

    public function checkAnswerType(AnswerDto $dto, ProjectProtocolQuestion $question):void
    {
        if($question->question->isAnswerText() && $dto->text == null){
            throw new ValidateException(__('exceptions.commercial.commissioning.answer must contain a text field'), 502, null, "questions.{$this->key}.text");
        }

        if(!$question->question->isAnswerText() && $dto->sendOptionAnswer == false){
            throw new ValidateException(__('exceptions.commercial.commissioning.answer must contain an options answer field'), 502,null, "questions.{$this->key}.text");
        }
    }

    public function checkPhotoType(AnswerDto $dto, ProjectProtocolQuestion $question): void
    {
        if($question->question->photoIsRequired() && $dto->sendMedia == false){
            throw new ValidateException(__('exceptions.commercial.commissioning.answer must contain a media'), 502, null, "questions.{$this->key}.media");
        }
    }

    public function checkRadioType(AnswerDto $dto, ProjectProtocolQuestion $question): void
    {
        if($question->question->isAnswerRadio() && count($dto->optionAnswerIds) > 1){
            throw new ValidateException(__('exceptions.commercial.commissioning.answer must contain one option answer'), 502, null, "questions.{$this->key}.option_answer_ids");
        }
    }

    public function checkApplyOptionAnswers(AnswerDto $dto, ProjectProtocolQuestion $question): void
    {
        $option = $question->question->optionAnswers->pluck('id', 'id')->toArray();

        foreach ($dto->optionAnswerIds as $id){
            if(!array_key_exists($id, $option)){
                throw new ValidateException(__('exceptions.commercial.commissioning.option answer does not apply to this question'), 502, null, "questions.{$this->key}.option_answer_ids");
                break;
            }
        }
    }
}



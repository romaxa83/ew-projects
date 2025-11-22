<?php

namespace App\Services\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use App\Models\Commercial\Commissioning\Question;
use App\Repositories\Commercial\Commissioning\ProjectProtocolQuestionRepository;
use App\Repositories\Commercial\Commissioning\ProjectProtocolRepository;
use App\Services\BaseService;
use App\Services\Commercial\CommercialProjectService;

class ProjectProtocolQuestionService extends BaseService
{
    public function __construct(
        protected CommercialProjectService $projectService,
        protected ProjectProtocolRepository $projectProtocolRepository,
        protected ProjectProtocolQuestionRepository $repo,
    )
    {
        parent::__construct();
    }

    public function attachQuestionToProjectProtocol(Question $question)
    {
        $projectProtocols = $this->projectProtocolRepository
            ->getAllForAttachQuestion($question);

        foreach ($projectProtocols as $projectProtocol){
            /** @var $projectProtocol ProjectProtocol */
            $this->create($question, $projectProtocol);
        }
    }

    public function detachQuestionFromProjectProtocol(Question $question)
    {
        foreach ($this->repo->getAllForRemove($question->id) as $item){
            /** @var $item ProjectProtocolQuestion */
            $item->delete();
        }
    }

    public function create(Question $question, ProjectProtocol $projectProtocol): ProjectProtocolQuestion
    {
        $model = new ProjectProtocolQuestion();
        $model->question_id = $question->id;
        $model->project_protocol_id = $projectProtocol->id;
        $model->answer_status = AnswerStatus::NONE;
        $model->sort = $question->sort;
        $model->save();

        return $model;
    }

    public function setStatus(ProjectProtocolQuestion $model, $status): ProjectProtocolQuestion
    {
        $model->answer_status = $status;
        $model->save();

        if($status === AnswerStatus::REJECT){
            if($model->projectProtocol->isPending()){
                $model->projectProtocol->status = ProtocolStatus::ISSUE;
                $model->projectProtocol->save();
            }

//            event(new AnswerRejectedEvent($model));
        }

        // если все ответы приняты, закрываем протокол
        if($status === AnswerStatus::ACCEPT  && $model->projectProtocol->canClose()){
            $model->projectProtocol->closeProtocol();

            // если протокол - pre_commissioning, протоколы закрыты, стартуем commissioning
            if(
                $model->projectProtocol->protocol->isPreCommissioning()
                && $this->projectService->canClosePreCommissioning($model->projectProtocol->project)
            ){
                $this->projectService->startCommissioning($model->projectProtocol->project);
            }
            // если протокол - commissioning, протоколы закрыты, закрываем commissioning
            if(
                $model->projectProtocol->protocol->isCommissioning()
                && $this->projectService->canCloseCommissioning($model->projectProtocol->project)
            ){
                $this->projectService->endCommissioning($model->projectProtocol->project);
            }
        }

        return $model;
    }
}



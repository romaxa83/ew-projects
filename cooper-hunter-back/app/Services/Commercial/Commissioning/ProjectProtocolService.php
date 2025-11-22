<?php

namespace App\Services\Commercial\Commissioning;

use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Models\Commercial\Commissioning\Protocol;
use App\Models\Commercial\Commissioning\Question;
use App\Repositories\Commercial\Commissioning\ProtocolRepository;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;

class ProjectProtocolService extends BaseService
{
    public function __construct(
        protected ProtocolRepository $protocolRepository,
        protected ProjectProtocolQuestionService $projectProtocolQuestionService
    )
    {
        parent::__construct();
    }

    public function attachProtocolsToProject(CommercialProject $project): void
    {
       $protocols = $this->protocolRepository->getAll();

       foreach ($protocols as $protocol){
           /** @var $protocol Protocol */
           $this->create($protocol, $project);
       }
    }

    public function attachProtocolToProjects(Collection $projects, Protocol $protocol)
    {
        foreach ($projects as $project){
            /** @var $project CommercialProject */
            $this->create($protocol, $project);
        }
    }

    public function create(Protocol $protocol, CommercialProject $project): ProjectProtocol
    {
        $model = new ProjectProtocol();
        $model->protocol_id = $protocol->id;
        $model->project_id = $project->id;
        $model->sort = $protocol->sort;
        $model->save();

        foreach ($protocol->questionsActive as $question){
            /** @var $question Question */
            $this->projectProtocolQuestionService
                ->create($question, $model);
        }

        return $model;
    }
}

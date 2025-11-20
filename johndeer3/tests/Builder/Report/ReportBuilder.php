<?php

namespace Tests\Builder\Report;

use App\DTO\Report\ReportDto;
use App\Models\Comment;
use App\Models\JD\Client;
use App\Models\JD\EquipmentGroup;
use App\Models\JD\ModelDescription;
use App\Models\Report\Location;
use App\Models\Report\Report;
use App\Models\Report\ReportClient;
use App\Models\Report\ReportMachine;
use App\Models\Report\ReportPushData;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\Report\ReportService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class ReportBuilder
{
    private $data = [];
    private $machineData = [];
    private $client = null;
    private $clientDataCustom = [];
    private $clientDataPivot = [];
    private $clientDataCustomPivot = [];
    private $locationData = [];
    private $features = [];
    private $pushData = [];
    private $comment = null;

    public function setStatus($value): self
    {
        $this->data['status'] = $value;
        return $this;
    }

    public function setClientEmail($value): self
    {
        $this->data['client_email'] = $value;
        return $this;
    }

    public function setComment($value): self
    {
        $this->comment = $value;
        return $this;
    }

    public function setModelDescription(ModelDescription $model, $useEG = true): self
    {
        if($useEG){
            $this->machineData['equipment_group_id'] = $model->equipmentGroup->id;
        }
        $this->machineData['model_description_id'] = $model->id;
        return $this;
    }

    public function setMachineData(array $value): self
    {
        $this->machineData = $value;
        return $this;
    }

    public function setEquipmentGroup(EquipmentGroup $model): self
    {
        $this->machineData['equipment_group_id'] = $model->id;
        return $this;
    }

    public function setCountry(string $value): self
    {
        $this->locationData['country'] = $value;
        return $this;
    }

    public function setLocationData(array $value): self
    {
        $this->locationData = $value;
        return $this;
    }

    public function setClientCustom(array $value, array $pivotValue = []): self
    {
        $this->clientDataCustom = $value;
        $this->clientDataCustomPivot = $pivotValue;
        return $this;
    }

    public function setClientJD(Client $model, array $pivotValue = []): self
    {
        $this->client = $model;
        $this->clientDataPivot = $pivotValue;
        return $this;
    }

    public function setUser(User $model): self
    {
        $this->data['user_id'] = $model->id;
        return $this;
    }

    public function setFeatures($value): self
    {
        $this->features = $value;
        return $this;
    }

    public function setPushData($value): self
    {
        $this->pushData = $value;
        return $this;
    }

    public function setCreatedAt(Carbon $value): self
    {
        $this->data['created_at'] = $value;
        return $this;
    }

    public function setTitle($value): self
    {
        $this->data['title'] = $value;
        return $this;
    }

    public function create()
    {
        if(!isset($this->data['user_id'])){
            $user = User::factory()->create();
            $user->roles()->attach(
                Role::query()->where('role', Role::ROLE_PS)->first()
            );
            $this->data['user_id'] = $user->id;
        }
        /** @var $model Report */
        $model = $this->save();

        if(!empty($this->machineData)){
            $datum = data_get($this->machineData, '0');
            if($datum == null){
                $machine = ReportMachine::factory()->create($this->machineData);
                $model->reportMachines()->attach($machine);
            } else {
                foreach ($this->machineData as $machineDatum){
                    $machine = ReportMachine::factory()->create($machineDatum);
                    $model->reportMachines()->attach($machine);
                }
            }
        }

        if(!empty($this->features)){
            \App(ReportService::class)
                ->saveFeatures(ReportDto::byRequest([
                    "features" => $this->features
                ])->setReportID($model->id));
            $model->fill_table_date = Carbon::now();
            $model->save();
        }

        if(!empty($this->locationData)){
            $this->locationData['report_id'] = $model->id;
            Location::factory()->create($this->locationData);
        }

        if(!empty($this->pushData)){
            $this->pushData['report_id'] = $model->id;
            ReportPushData::factory()->create($this->pushData);
        }

        if($this->client){
            $model->clients()->attach($this->client, $this->clientDataPivot);
        }

        if($this->comment){
            $this->createComment($model);
        }

        if(!empty($this->clientDataCustom)){
            $client = ReportClient::factory()->create($this->clientDataCustom);
            $model->reportClients()->attach($client, $this->clientDataCustomPivot);
        }

        $this->clear();

        return $model;
    }

    private function save()
    {
        CarbonImmutable::setTestNow(Carbon::now()->subYear());

        return Report::factory()->new($this->data)->create();
    }

    private function clear()
    {
        $this->data = [];
        $this->machineData = [];
        $this->locationData = [];
        $this->features = [];
        $this->clientDataPivot = [];
        $this->clientDataCustomPivot = [];
        $this->clientDataCustom = [];
        $this->client = null;
        $this->pushData = [];
        $this->comment = null;
    }

    private function createComment($model)
    {
        $comment = new Comment();
        $comment->model = Comment::COMMENT_BY_REPORT;
        $comment->entity_type = Report::class;
        $comment->entity_id = $model->id;
        $comment->text = $this->comment;
        $comment->author_id = $this->data['user_id'];
        $comment->save();
    }
}

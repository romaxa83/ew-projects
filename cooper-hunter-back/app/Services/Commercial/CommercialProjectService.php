<?php

namespace App\Services\Commercial;

use App\Contracts\Members\HasCommercialProjects;
use App\Dto\Commercial\CommercialProjectDto;
use App\Enums\Commercial\CommercialProjectStatusEnum;
use App\Enums\Formats\DatetimeEnum;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Models\Commercial\Commissioning\Protocol;
use App\Repositories\Commercial\CommercialProjectRepository;
use App\Services\Commercial\Commissioning\ProjectProtocolService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CommercialProjectService
{
    public const CODE_LENGTH = 6;

    public function create(
        HasCommercialProjects $user,
        CommercialProjectDto $dto
    ): CommercialProject {
        return $this->store($user, new CommercialProject(), $dto);
    }

    public function update(
        CommercialProject $model,
        CommercialProjectDto $dto
    ): CommercialProject {
        return $this->edit($model, $dto);
    }

    private function store(
        HasCommercialProjects $user,
        CommercialProject $project,
        CommercialProjectDto $dto
    ): CommercialProject {
        $this->setMember($project, $user);
        $this->fill($project, $dto);

        $this->resolveStatus($project);
        $this->generateCodeIfNecessary($project);

        $project->request_until = now()->add(config('commercial.rdp.credentials.make_request_until'))->endOfDay();

        $project->save();

        return $project;
    }

    private function edit(
        CommercialProject $project,
        CommercialProjectDto $dto
    ): CommercialProject {

        $this->fill($project, $dto);

        $this->updateRequestUntilTime($project, $dto->requestUntil, false);

        $project->save();

        return $project;
    }

    private function setMember(CommercialProject $project, HasCommercialProjects $user): void
    {
        $project->member_type = $user->getMorphType();
        $project->member_id = $user->getId();
    }

    private function fill(CommercialProject $project, CommercialProjectDto $dto): void
    {
        $project->name = $dto->getName();
        $project->address_line_1 = $dto->getAddressLine1();
        $project->address_line_2 = $dto->getAddressLine2();
        $project->city = $dto->getCity();
        $project->state_id = $dto->getStateId();
        $project->country_id = $dto->getCountryId();
        $project->zip = $dto->getZip();
        $project->first_name = $dto->getFirstName();
        $project->last_name = $dto->getLastName();
        $project->phone = $dto->getPhone();
        $project->email = $dto->getEmail();
        $project->company_name = $dto->getCompanyName();
        $project->company_address = $dto->getCompanyAddress();
        $project->description = $dto->getDescription();
        $project->estimate_start_date = $dto->getEstimateStartDate();
        $project->estimate_end_date = $dto->getEstimateEndDate();

        $project->address_hash = $this->getAddressHash($project);
    }

    public function getAddressHash(CommercialProject $project): string
    {
        return sha1(
            sprintf(
                '%s-%s-%s-%s-%s',
                $this->hashString($project->address_line_1),
                $this->hashString($project->city),
                $this->hashString($project->state_id),
                $this->hashString($project->country_id),
                $this->hashString($project->zip),
            )
        );
    }

    public function hashString(string $string): string
    {
        return Str::slug($string, '');
    }

    private function resolveStatus(CommercialProject $project): void
    {
        $previous = CommercialProject::query()
            ->when($project->exists, fn(Builder $builder) => $builder->whereKeyNot($project->getKey()))
            ->where('address_hash', $project->address_hash)
            ->max('id');

        if (!$previous) {
            $project->status = CommercialProjectStatusEnum::PENDING();

            return;
        }

        $project->previous()->associate($previous);
        $project->status = CommercialProjectStatusEnum::CREATED();
    }

    private function generateCodeIfNecessary(CommercialProject $project): void
    {
        if (!$project->code && $project->status->isPending()) {
            $project->code = $this->generateUniqueCode();
        }
    }

    private function generateUniqueCode(?int $length = null): string
    {
        if (is_null($length)) {
            $length = self::CODE_LENGTH;
        }

        $code = Str::random($length);

        if (CommercialProject::query()->where('code', $code)->exists()) {
            $length = min(++$length, self::CODE_LENGTH + 4);

            $code = $this->generateUniqueCode($length);
        }

        return Str::upper($code);
    }

    public function updateName(CommercialProject $project, string $name): CommercialProject
    {
        $project->name = $name;
        $project->save();

        return $project;
    }

    public function delete(CommercialProject $project): bool
    {
        return $project->delete();
    }

    public function updateRequestUntilTime(CommercialProject $project, string $requestUntil, $save = true): CommercialProject
    {
        $project->request_until = Carbon::createFromFormat(DatetimeEnum::DATE, $requestUntil)->endOfDay();
        if($save){
            $project->save();
        }

        return $project;
    }

    public function startPreCommissioning(CommercialProject $model): CommercialProject
    {
        if($model->start_pre_commissioning_date){
            throw new \Exception(__('messages.commercial.is_commissioning_start'), 400);
        }

        $model->start_pre_commissioning_date = CarbonImmutable::now();

        $model->save();

        resolve(ProjectProtocolService::class)->attachProtocolsToProject($model);

        logger("START PRE_COMMISSIONING - [project - {$model->id}]");

        return $model;
    }

    public function canClosePreCommissioning(CommercialProject $model): bool
    {
        $can = true;
        foreach ($model->projectProtocolsPreCommissioning as $item){
            /** @var $item ProjectProtocol */
            if(!$item->isDone()) {
                $can = false;
                continue;
            }
        }

        return $can;
    }

    public function startCommissioning(CommercialProject $model): CommercialProject
    {
        if($model->start_commissioning_date){
            throw new \Exception("This project is start commissioning", 400);
        }

        $model->end_pre_commissioning_date = CarbonImmutable::now();
        $model->start_commissioning_date = CarbonImmutable::now();

        $model->save();

        logger("START COMMISSIONING - [project - {$model->id}]");

        return $model;
    }

    public function canCloseCommissioning(CommercialProject $model): bool
    {
        foreach ($model->projectProtocolsCommissioning as $item){
            /** @var $item ProjectProtocol */
            $can = true;
            if(!$item->isDone()) {
                $can = false;
                continue;
            }
        }

        return $can;
    }

    public function endCommissioning(CommercialProject $model): CommercialProject
    {
        if($model->end_commissioning_date){
            throw new \Exception("This project is end commissioning", 400);
        }

        $model->end_commissioning_date = CarbonImmutable::now();

        $model->save();

        logger("END COMMISSIONING - [project - {$model->id}]");

        return $model;
    }

    public function createOrUpdateUnits(CommercialProject $model): CommercialProject
    {
        if($model->end_commissioning_date){
            throw new \Exception("This project is end commissioning", 400);
        }

        $model->end_commissioning_date = CarbonImmutable::now();

        $model->save();

        logger("END COMMISSIONING - [project - {$model->id}]");

        return $model;
    }

    public function addNewProtocol(Protocol $protocol): void
    {
        /** @var $service ProjectProtocolService */
        $service = resolve(ProjectProtocolService::class);
        /** @var $repo CommercialProjectRepository */
        $repo = resolve(CommercialProjectRepository::class);

        $projects = new Collection();
        if($protocol->isPreCommissioning()){
            $projects = $repo->projectsPreCommissioning();
        }
        if($protocol->isCommissioning()){
            $projects = $repo->projectsCommissioning();
        }

        if($projects->isNotEmpty()){
            $service->attachProtocolToProjects($projects, $protocol);
        }
    }
}

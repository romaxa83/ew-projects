<?php

namespace App\Services\Companies;

use App\Dto\Companies\CorporationDto;
use App\Models\Companies\Corporation;
use App\Repositories\Companies\CorporationRepository;

class CorporationService
{
    public function __construct(protected CorporationRepository $repo)
    {}

    public function createOrGet(CorporationDto $dto): Corporation
    {
        if($corp = $this->repo->getBy('guid', $dto->guid)){
            /** @var $corp Corporation */
            return $corp;
        }

        return $this->create($dto);
    }

    public function create(CorporationDto $dto): Corporation
    {
        $model = new Corporation();
        $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    protected function fill(Corporation $model, CorporationDto $dto): void
    {
        $model->guid = $dto->guid;
        $model->name = $dto->name;
    }
}

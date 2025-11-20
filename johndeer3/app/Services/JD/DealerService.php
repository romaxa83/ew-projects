<?php

namespace App\Services\JD;

use App\DTO\JD\DealerDTO;
use App\Models\JD\Dealer;
use App\Repositories\NationalityRepository;

class DealerService
{
    public function __construct(protected NationalityRepository $nationalityRepository)
    {}

    public function createFromImport(DealerDTO $dto) : Dealer
    {
        $model = new Dealer();
        $model->jd_id = $dto->jdID;
        $model->jd_jd_id = $dto->jdjdID;
        $model->name = $dto->name;
        $model->country = $dto->country;
        $model->status = $dto->status;
        $model->nationality_id = $this->setNationality($dto->country);

        $model->save();

        return $model;
    }

    public function updateFromImport(Dealer $model, DealerDTO $dto) : Dealer
    {
        $model->jd_id = $dto->jdID;
        $model->jd_jd_id = $dto->jdjdID;
        $model->name = $dto->name;
        $model->country = $dto->country;
        $model->status = $dto->status;
        $model->nationality_id = $this->setNationality($dto->country);

        $model->save();

        return $model;
    }

    private function setNationality(?string $country): ?string
    {
        if(!$country){
            return null;
        }

        $nationals = array_flip($this->nationalityRepository->getFoSelect());
        $name = explode('-', $country);

        return array_key_exists(trim($name[1]), $nationals)
            ? $nationals[trim($name[1])]
            : null;
    }

    public function edit(array $data, Dealer $model) : Dealer
    {
        // todo - по идеи это tm, нужно проверить на роль
        if(array_key_exists('user_ids', $data)){
            $model->users()->detach();
            if(!empty($data['user_ids']) && $data['user_ids'] !== null){
                $model->users()->attach($data['user_ids']);
            }
        }

        return $model->refresh();
    }
}

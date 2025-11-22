<?php

namespace App\Services\Order;

use App\Models\Agreement\Agreement;
use App\Models\Agreement\Job;
use App\Models\Agreement\Part;
use App\Services\BaseService;
use App\ValueObjects\CarNumber;
use App\ValueObjects\CarVin;
use App\ValueObjects\Phone;
use Carbon\CarbonImmutable;

class AgreementService extends BaseService
{
    public function __construct()
    {}

    public function create(array $data): Agreement
    {
        $model = new Agreement();
        $model->uuid = $data["id"];
        $model->user_uuid = $data["client"];
        $model->car_uuid = $data["auto"];
        $model->phone = new Phone($data["phone"]);
        $model->number = new CarNumber($data['number']);
        $model->vin = new CarVin($data['VIN']);
        $model->author = $data["author"] ?? null;
        $model->author_phone = $data["authorPhone"] ?? null;
        $model->dealership_alias = $data["base"] ?? null;
        $model->base_order_uuid = $data["idRequst"] ?? null;

        $model->save();

        $this->saveJobs($data['jobs'] ?? [], $model->id);
        $this->saveParts($data['parts'] ?? [], $model->id);

        return $model;
    }

    public function edit(Agreement $model, array $data): Agreement
    {
        $model->uuid = $data["id"];
        $model->user_uuid = $data["client"];
        $model->car_uuid = $data["auto"];
        $model->phone = new Phone($data["phone"]);
        $model->number = new CarNumber($data['number']);
        $model->vin = new CarVin($data['VIN']);
        $model->author = $data["author"] ?? $model->author;
        $model->author_phone = $data["authorPhone"] ?? $model->author_phone;
        $model->dealership_alias = $data["base"] ?? $model->dealership_alias;
        $model->base_order_uuid = $data["idRequst"] ?? $model->base_order_uuid;

        $model->save();

        $model->jobs()->delete();
        $model->parts()->delete();

        $this->saveJobs($data['jobs'] ?? [], $model->id);
        $this->saveParts($data['parts'] ?? [], $model->id);

        return $model;
    }

    public function setStatus(Agreement $model, $status): Agreement
    {
        $model->status = $status;
        if($status == Agreement::STATUS_VERIFY){
            $model->accepted_at = CarbonImmutable::now();
        }

        $model->save();

        return $model;
    }

    private function saveJobs(array $data, $ID): void
    {
        foreach ($data as $item){
            $model = new Job();
            $model->name = $item['name'];
            $model->sum = $item['sum'];
            $model->agreement_id = $ID;
            $model->save();
        }
    }

    private function saveParts(array $data, $ID): void
    {
        foreach ($data as $item){
            $model = new Part();
            $model->name = $item['name'];
            $model->sum = $item['sum'];
            $model->qty = $item['quantity'];
            $model->agreement_id = $ID;
            $model->save();
        }
    }

    public function setErrorFromAA(Agreement $model): Agreement
    {
        $model->status = Agreement::STATUS_ERROR;
        $model->order()->delete();

        $model->save();

        return $model;
    }

    public function remove(Agreement $model): void
    {
        $model->forceDelete();
    }
}




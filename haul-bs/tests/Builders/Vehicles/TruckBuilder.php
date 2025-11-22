<?php

namespace Tests\Builders\Vehicles;

use App\Models\Companies\Company;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Truck;
use App\Services\Vehicles\TruckService;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use phpseclib3\File\ASN1\Maps\AttributeTypeAndValue;
use Tests\Builders\BaseBuilder;

class TruckBuilder extends BaseBuilder
{
    protected array $tags = [];
    protected array $files = [];

    function modelClass(): string
    {
        return Truck::class;
    }

    public function vin(string $value): self
    {
        $this->data['vin'] = $value;
        return $this;
    }

    public function year(string $value): self
    {
        $this->data['year'] = $value;
        return $this;
    }

    public function make(string $value): self
    {
        $this->data['make'] = $value;
        return $this;
    }

    public function model(string $value): self
    {
        $this->data['model'] = $value;
        return $this;
    }

    public function unit_number(string $value): self
    {
        $this->data['unit_number'] = $value;
        return $this;
    }

    public function license_plate(string $value): self
    {
        $this->data['license_plate'] = $value;
        return $this;
    }

    public function temporary_plate(string $value): self
    {
        $this->data['temporary_plate'] = $value;
        return $this;
    }

    public function customer(Customer $model): self
    {
        $this->data['customer_id'] = $model;
        return $this;
    }

    public function company(Company $model): self
    {
        $this->data['company_id'] = $model;
        return $this;
    }

    public function tags(Tag ...$models): self
    {
        $this->tags = $models;
        return $this;
    }

    public function attachments(UploadedFile ...$models): self
    {
        $this->files = $models;
        return $this;
    }

    public function delete(): self
    {
        $this->data['deleted_at'] = CarbonImmutable::now();
        return $this;
    }

    public function origin_id(int $value): self
    {
        $this->data['origin_id'] = $value;
        return $this;
    }

    protected function afterSave($model): void
    {
        /** @var $model Truck */
        if(!empty($this->tags)){
            $ids = [];
            foreach ($this->tags as $tag){
                $ids[] = $tag->id;
            }

            $model->tags()->sync($ids);
        }
        if(!empty($this->files)){
            /** @var $service TruckService */
            $service = resolve(TruckService::class);
            $service->uploadFiles($model, $this->files);
        }
    }

    protected function afterClear(): void
    {
        $this->tags = [];
        $this->files = [];
    }
}

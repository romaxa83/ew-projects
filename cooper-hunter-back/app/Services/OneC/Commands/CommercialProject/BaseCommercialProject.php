<?php

namespace App\Services\OneC\Commands\CommercialProject;

use App\Contracts\Utilities\Dispatchable;
use App\Enums\Warranties\WarrantyType;
use App\Models\Commercial\CommercialProject;
use App\Services\OneC\Commands\BaseCommand;

class BaseCommercialProject extends BaseCommand
{
    public function transformData(Dispatchable $model, array $additions = []): array
    {
        /** @var $model CommercialProject */
        return [
            'id' => $model->id,
            'guid' => $model->guid,
            'name' => $model->name,
            'status' => $model->status->value,
            'type' => WarrantyType::COMMERCIAL,
            'address_line_1' => $model->address_line_1,
            'address_line_2' => $model->address_line_2,
            'city' => $model->city,
            'country' => $model->country->country_code,
            'state' => $model->state->short_name,
            'zip' => $model->zip,
            'first_name' => $model->first_name,
            'last_name' => $model->last_name,
            'phone' => $model->phone->getValue(),
            'email' => $model->email->getValue(),
            'company_name' => $model->company_name,
            'company_address' => $model->company_address,
            'description' => $model->description,
            'estimate_start_date' => $model->estimate_start_date?->format($this->formatDate),
            'estimate_end_date' => $model->estimate_end_date?->format($this->formatDate),
            'created_at' => $model->created_at?->format($this->formatDate),
            'request_until' => $model->request_until?->format($this->formatDate),
            'technician' => [
                'guid' => $model->member->guid,
                'name' => $model->member->full_name,
                'email' => $model->member->email->getValue(),
            ]
        ];
    }

    protected function nameCommand(): string
    {
        return '';
    }

    protected function getUri(): string
    {
        return '';
    }
}


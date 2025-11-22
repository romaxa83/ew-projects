<?php

namespace App\Services\OneC\Commands\Company;

use App\Contracts\Utilities\Dispatchable;
use App\Models\Companies\Company;
use App\Models\Companies\ShippingAddress;
use App\Models\Media\Media;
use App\Services\OneC\Commands\BaseCommand;

class BaseCompanyCommand extends BaseCommand
{
    public function transformData(Dispatchable $model, array $additions = []): array
    {
        $address = [];
        /** @var $model Company */
        foreach ($model->shippingAddresses as $k => $item){
            /** @var $item ShippingAddress */
            $address[$k]['name'] = $item->name;
            $address[$k]['phone'] = $item->phone->getValue();
            $address[$k]['fax'] = $item->fax?->getValue();
            $address[$k]['email'] = $item->email->getValue();
            $address[$k]['receiving_persona'] = $item->receiving_persona;
            $address[$k]['country'] = $item->country->country_code;
            $address[$k]['state'] = $item->state->short_name;
            $address[$k]['city'] = $item->city;
            $address[$k]['address_line_1'] = $item->address_line_1;
            $address[$k]['address_line_2'] = $item->address_line_2;
            $address[$k]['zip'] = $item->zip;
        }

        $media = [];
        foreach ($model->media as $item){
            /** @var $item Media */
            $media[] = $item->getFullUrl();
        }

        return [
            'id' => $model->id,
            'guid' => $model->guid,
            'authorization_code' => $model->code,
            'type' => $model->type->value,
            'status' => $model->status->value,
            'terms' => $model->terms,
            'business_name' => $model->business_name,
            'email' => $model->email->getValue(),
            'phone' => $model->phone?->getValue(),
            'country' => $model->country->country_code,
            'state' => $model->state->short_name,
            'city' => $model->city,
            'address_line_1' => $model->address_line_1,
            'address_line_2' => $model->address_line_2,
            'po_box' => $model->po_box,
            'zip' => $model->zip,
            'taxpayer_id' => $model->taxpayer_id,
            'tax' => $model->tax,
            'websites' => $model->websites,
            'marketplaces' => $model->marketplaces,
            'trade_names' => $model->trade_names,
            'created_at' => $model->created_at?->format($this->formatDate),
            'contact_account' => [
                'name' => $model->contactAccount->name,
                'phone' => $model->contactAccount->phone->getValue(),
                'email' => $model->contactAccount->email->getValue(),
                'country' => $model->contactAccount->country->country_code,
                'state' => $model->contactAccount->state->short_name,
                'city' => $model->contactAccount->city,
                'address_line_1' => $model->contactAccount->address_line_1,
                'address_line_2' => $model->contactAccount->address_line_2,
                'po_box' => $model->contactAccount->po_box,
                'zip' => $model->contactAccount->zip,
            ],
            'contact_order' => [
                'name' => $model->contactOrder->name,
                'phone' => $model->contactOrder->phone->getValue(),
                'email' => $model->contactOrder->email->getValue(),
                'country' => $model->contactOrder->country->country_code,
                'state' => $model->contactOrder->state->short_name,
                'city' => $model->contactOrder->city,
                'address_line_1' => $model->contactOrder->address_line_1,
                'address_line_2' => $model->contactOrder->address_line_2,
                'po_box' => $model->contactOrder->po_box,
                'zip' => $model->contactOrder->zip,
            ],
            'locations' => $address,
            'media' => $media
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


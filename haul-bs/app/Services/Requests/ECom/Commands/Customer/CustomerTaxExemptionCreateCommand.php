<?php

namespace App\Services\Requests\ECom\Commands\Customer;

use App\Foundations\Modules\Media\Services\ImageResourceTransformer;
use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use App\Models\Customers\CustomerTaxExemption;
use App\Models\Inventories\Brand;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class CustomerTaxExemptionCreateCommand extends EComBaseCommand
{
    use TransformFullUrl;

    public function getUri(array $data = null): string
    {
        return str_replace('{email}', $data['email'], config("requests.e_com.paths.customer_tax_exemption.create"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Post;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data CustomerTaxExemption */
        return [
            'date_active_to' => $data->date_active_to->format('m-d-Y'),
            'link' => $this->fullUrl($data->file),
            'file_name' => $data->file->file_name,
            'email' => $data->customer->email
        ];
    }
}

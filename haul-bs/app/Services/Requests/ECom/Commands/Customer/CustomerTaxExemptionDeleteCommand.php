<?php

namespace App\Services\Requests\ECom\Commands\Customer;

use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use App\Models\Customers\Customer;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class CustomerTaxExemptionDeleteCommand extends EComBaseCommand
{
    use TransformFullUrl;

    public function getUri(array $data = null): string
    {
        return str_replace('{email}', $data['email'], config("requests.e_com.paths.customer_tax_exemption.delete"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Delete;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Customer */
        return [
            'email' => $data->email
        ];
    }
}

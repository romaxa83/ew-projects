<?php

namespace App\Services\Requests\ECom\Commands\Customer;

use App\Models\Customers\Customer;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class CustomerSetTagEcomCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        return config("requests.e_com.paths.customer.set_tag_ecomm");
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Post;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Customer */
        $tmp = [
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'email' => $data->email->getValue(),
        ];

        return $tmp;
    }

    public function afterRequest(array $res): mixed
    {
        return $res['data'];
    }
}

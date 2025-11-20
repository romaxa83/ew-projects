<?php

namespace App\Services\JD;

use App\Abstractions\AbstractService;
use App\Models\JD\Client;

class ClientService extends AbstractService
{
    public function createFromImport(array $data) : Client
    {
        $client = new Client();
        $client->jd_id = $data['id'];
        $client->customer_id = $data['customer_id'];
        $client->company_name = $data['company_name'];
        $client->customer_first_name = $data['customer_first_name'];
        $client->customer_last_name = $data['customer_last_name'];
        $client->customer_second_name = $data['customer_second_name'];
        $client->phone = $data['phone'];
        $client->status = $data['status'];
        $client->created_at = $data['created_at'];
        $client->region_id = $data['region_id'];

        $client->save();

        return $client;
    }

    public function updateFromImport(array $data, Client $client) : Client
    {
        $client->customer_id = $data['customer_id'];
        $client->company_name = $data['company_name'];
        $client->customer_first_name = $data['customer_first_name'];
        $client->customer_last_name = $data['customer_last_name'];
        $client->customer_second_name = $data['customer_second_name'];
        $client->phone = $data['phone'];
        $client->status = $data['status'];
        $client->created_at = $data['created_at'];
        $client->region_id = $data['region_id'];

        $client->save();

        return $client;
    }
}

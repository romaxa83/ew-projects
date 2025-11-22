<?php

namespace Tests\Builders\Settings;

use App\Services\Settings\SettingService;

class SettingBuilder
{
    protected array $data = [
        'company_name' => 'ADESA LAS VEGAS',
        'address' => '1395 E 4th St, Reno, NV 89512',
        'city' => 'Reno',
        'state_id' => 1,
        'zip' => '89512',
        'timezone' => 'America/Los_Angeles',
        'phone_name' => 'John Doe',
        'phone' => '12999999999',
        'phone_extension' => '999',
        'phones' => [
            [
                'name' => 'Walls',
                'number' => '13999999999',
                'extension' => '139',
            ]
        ],
        'email' => 'jack@mail.com',
        'fax' => '(248) 721-4985',
        'website' => 'www.company.com',
        'billing_phone' => '1555555555',
        'billing_phone_name' => 'John Doe',
        'billing_phone_extension' => '3333',
        'billing_phones' => [
            [
                'name' => 'Alex',
                'number' => '13999999999',
                'extension' => '139',
            ]
        ],
        'billing_email' => 'mail@server.net',
        'billing_payment_details' => 'Some payment details',
        'billing_terms' => 'Some carrier terms',
    ];


    public function create()
    {
        /** @var $service SettingService */
        $service = resolve(SettingService::class);
        $service->update($this->data);
    }
}


<?php

namespace Tests\Traits;

use App\Services\Settings\SettingService;
use Tests\Builders\Location\StateBuilder;

trait SettingsData
{
    public array $settings = [
        'company_name' => 'ADESA LAS VEGAS',
        'address' => '1395 E 4th St, Reno, NV 89512',
        'city' => 'Reno',
        'state_id' => 1,
        'zip' => '89512',
        'timezone' => 'America/Chicago',
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
        'ecommerce_company_name' => 'eADESA LAS VEGAS',
        'ecommerce_address' => '1395 eE 4th St, Reno, NV 89512',
        'ecommerce_city' => 'eReno',
        'ecommerce_state_id' => 1,
        'ecommerce_zip' => '79512',
        'ecommerce_phone_name' => 'eJohn Doe',
        'ecommerce_phone' => '12999999969',
        'ecommerce_phone_extension' => '99',
        'ecommerce_phones' => [
            [
                'name' => 'eWalls',
                'number' => '14999999999',
                'extension' => '39',
            ]
        ],
        'ecommerce_email' => 'ejack@mail.com',
        'ecommerce_fax' => '(248) 627-4985',
        'ecommerce_website' => 'www.becompany.com',
        'ecommerce_billing_phone' => '1555575555',
        'ecommerce_billing_phone_name' => 'ebJohn Doe',
        'ecommerce_billing_phone_extension' => '2333',
        'ecommerce_billing_phones' => [
            [
                'name' => 'ebAlex',
                'number' => '12999999999',
                'extension' => '239',
            ]
        ],
        'ecommerce_billing_email' => 'ebmail@server.net',
        'ecommerce_billing_payment_details' => 'ebSome payment details',
        'ecommerce_billing_terms' => 'ebSome carrier terms',
        'ecommerce_billing_payment_options' => 'ebSome payments options',
    ];

    public function setSettings(): array
    {
        $stateBuilder = resolve(StateBuilder::class);
        $state = $stateBuilder->create();

        $service = resolve(SettingService::class);

        $this->settings['state_id'] = $state->id;
        $this->settings['ecommerce_state_id'] = $state->id;

        $service->update($this->settings);

        return $this->settings;
    }
}

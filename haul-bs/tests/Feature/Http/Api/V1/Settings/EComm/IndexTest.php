<?php

namespace Tests\Feature\Http\Api\V1\Settings\EComm;

use App\Foundations\Modules\Location\Models\State;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\SettingsData;

class IndexTest extends TestCase
{
    use DatabaseTransactions;
    use SettingsData;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_list()
    {
        $settingData = $this->setSettings();
        $this->getJson(route('api.v1.e_comm.settings.index'), [
            'Authorization' => config('api.e_comm.token')
        ])
            ->assertJson([
                'data' => [
                    'ecommerce_company_name' => $settingData['ecommerce_company_name'],
                    'ecommerce_address' => $settingData['ecommerce_address'],
                    'ecommerce_city' => $settingData['ecommerce_city'],
                    'ecommerce_state_name' => State::find($settingData['ecommerce_state_id'])->name,
                    'ecommerce_zip' => $settingData['ecommerce_zip'],
                    'ecommerce_phone' => $settingData['ecommerce_phone'],
                    'ecommerce_phone_name' => $settingData['ecommerce_phone_name'],
                    'ecommerce_phone_extension' => $settingData['ecommerce_phone_extension'],
                    'ecommerce_phones' => $settingData['ecommerce_phones'],
                    'ecommerce_email' => $settingData['ecommerce_email'],
                    'ecommerce_fax' => $settingData['ecommerce_fax'],
                    'ecommerce_website' => $settingData['ecommerce_website'],
                    'ecommerce_billing_phone' => $settingData['ecommerce_billing_phone'],
                    'ecommerce_billing_phone_name' => $settingData['ecommerce_billing_phone_name'],
                    'ecommerce_billing_phone_extension' => $settingData['ecommerce_billing_phone_extension'],
                    'ecommerce_billing_phones' => $settingData['ecommerce_billing_phones'],
                    'ecommerce_billing_email' => $settingData['ecommerce_billing_email'],
                    'ecommerce_billing_payment_details' => $settingData['ecommerce_billing_payment_details'],
                    'ecommerce_billing_terms' => $settingData['ecommerce_billing_terms'],
                    'ecommerce_billing_payment_options' => $settingData['ecommerce_billing_payment_options'],
                ]
            ])
        ;
    }

    /** @test */
    public function wrong_token()
    {
        $res = $this->getJson(route('api.v1.e_comm.settings.index'), [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}

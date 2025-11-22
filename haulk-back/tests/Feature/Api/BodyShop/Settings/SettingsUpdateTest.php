<?php

namespace Api\BodyShop\Settings;

use App\Models\Locations\State;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SettingsUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_not_update_for_unauthorized_users()
    {
        $this->putJson(route('body-shop.settings.update-info'))->assertUnauthorized();
    }

    public function test_it_update_by_bs_super_admin()
    {
        $state = factory(State::class)->create();
        $formRequest = [
            'company_name' => 'Test name',
            'address' => 'test address',
            'city' => 'test city',
            'state_id' => $state->id,
            'zip' => '3454',
            'timezone' => 'America/Los_Angeles',
            'phone' => '3456723455',
            'phone_name' => 'test',
            'phone_extension' => 'ext',
            'phones' => [
                [
                    'name' => 'test 1',
                    'number' => '3443423423',
                    'extension' => '34'
                ],
            ],
            'email' => 'test@test.com',
            'fax' => '35345435',
            'website' => 'test.com',
            'billing_phone' => '435345345345',
            'billing_phone_name' => 'test bill',
            'billing_phone_extension' => 'test bill ext',
            'billing_phones' => [],
            'billing_email' => 'test@test.coom',
            'billing_payment_details' => 'text',
            'billing_terms' => 'terms text',
        ];


        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.settings.update-info'), $formRequest)
            ->assertOk();
    }
}

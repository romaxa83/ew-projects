<?php

namespace Tests\Feature\Http\Api\V1\Settings\SettingCrud;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InfoTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_info()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.settings.info'))
            ->assertJsonStructure([
                'data' => [
                    'company_name',
                    'address',
                    'city',
                    'state_id',
                    'zip',
                    'timezone',
                    'phone',
                    'phone_name',
                    'phone_extension',
                    'phones',
                    'email',
                    'fax',
                    'website',
                    'billing_phone',
                    'billing_phone_name',
                    'billing_phone_extension',
                    'billing_phones',
                    'billing_email',
                    'billing_payment_details',
                    'billing_terms',
                    'logo',
                    'ecommerce_logo',
                    'ecommerce_company_name',
                    'ecommerce_address',
                    'ecommerce_city',
                    'ecommerce_state_id',
                    'ecommerce_zip',
                    'ecommerce_phone',
                    'ecommerce_phone_name',
                    'ecommerce_phone_extension',
                    'ecommerce_phones',
                    'ecommerce_email',
                    'ecommerce_fax',
                    'ecommerce_website',
                    'ecommerce_billing_phone',
                    'ecommerce_billing_phone_name',
                    'ecommerce_billing_phone_extension',
                    'ecommerce_billing_phones',
                    'ecommerce_billing_email',
                    'ecommerce_billing_payment_details',
                    'ecommerce_billing_terms',
                    'ecommerce_billing_payment_options',
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.settings.info'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.settings.info'));

        self::assertUnauthenticatedMessage($res);
    }
}

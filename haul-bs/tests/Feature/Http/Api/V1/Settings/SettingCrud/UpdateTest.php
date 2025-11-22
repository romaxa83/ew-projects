<?php

namespace Tests\Feature\Http\Api\V1\Settings\SettingCrud;

use App\Events\Events\Settings\RequestToEcom;
use App\Events\Listeners\Settings\RequestToEcomListener;
use App\Foundations\Modules\Location\Models\State;
use App\Models\Settings\Settings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Location\StateBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected StateBuilder $stateBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->stateBuilder = resolve(StateBuilder::class);

        $state = State::find(1) ?? $this->stateBuilder->create();

        $this->data = [
            'company_name' => 'ADESA LAS VEGAS',
            'address' => '1395 E 4th St, Reno, NV 89512',
            'city' => 'Reno',
            'state_id' => $state->id,
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
            'ecommerce_state_id' => $state->id,
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
            'ecommerce_billing_payment_options' => 'ebSome payment options',
        ];
    }

    /** @test */
    public function success_update()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        foreach ($data as $key => $value){
            $this->assertNotEquals(Settings::getParam($key), $value);
        }

        $this->putJson(route('api.v1.settings.update'), $data)
            ->assertJson([
                'data' => [
                    'company_name' => data_get($data, 'company_name'),
                    'address' => data_get($data, 'address'),
                    'city' => data_get($data, 'city'),
                    'state_id' => data_get($data, 'state_id'),
                    'zip' => data_get($data, 'zip'),
                    'timezone' => data_get($data, 'timezone'),
                    'phone' => data_get($data, 'phone'),
                    'phone_name' => data_get($data, 'phone_name'),
                    'phone_extension' => data_get($data, 'phone_extension'),
                    'phones' => data_get($data, 'phones'),
                    'fax' => data_get($data, 'fax'),
                    'website' => data_get($data, 'website'),
                    'billing_phone' => data_get($data, 'billing_phone'),
                    'billing_phone_name' => data_get($data, 'billing_phone_name'),
                    'billing_phone_extension' => data_get($data, 'billing_phone_extension'),
                    'billing_phones' => data_get($data, 'billing_phones'),
                    'billing_email' => data_get($data, 'billing_email'),
                    'billing_payment_details' => data_get($data, 'billing_payment_details'),
                    'billing_terms' => data_get($data, 'billing_terms'),
                    'logo' => null,
                    'ecommerce_logo' => null,
                    'ecommerce_company_name' => data_get($data, 'ecommerce_company_name'),
                    'ecommerce_address' => data_get($data, 'ecommerce_address'),
                    'ecommerce_city' => data_get($data, 'ecommerce_city'),
                    'ecommerce_state_id' => data_get($data, 'ecommerce_state_id'),
                    'ecommerce_zip' => data_get($data, 'ecommerce_zip'),
                    'ecommerce_phone' => data_get($data, 'ecommerce_phone'),
                    'ecommerce_phone_name' => data_get($data, 'ecommerce_phone_name'),
                    'ecommerce_phone_extension' => data_get($data, 'ecommerce_phone_extension'),
                    'ecommerce_phones' => data_get($data, 'ecommerce_phones'),
                    'ecommerce_fax' => data_get($data, 'ecommerce_fax'),
                    'ecommerce_website' => data_get($data, 'ecommerce_website'),
                    'ecommerce_billing_phone' => data_get($data, 'ecommerce_billing_phone'),
                    'ecommerce_billing_phone_name' => data_get($data, 'ecommerce_billing_phone_name'),
                    'ecommerce_billing_phone_extension' => data_get($data, 'ecommerce_billing_phone_extension'),
                    'ecommerce_billing_phones' => data_get($data, 'ecommerce_billing_phones'),
                    'ecommerce_billing_email' => data_get($data, 'ecommerce_billing_email'),
                    'ecommerce_billing_payment_details' => data_get($data, 'ecommerce_billing_payment_details'),
                    'ecommerce_billing_terms' => data_get($data, 'ecommerce_billing_terms'),
                    'ecommerce_billing_payment_options' => data_get($data, 'ecommerce_billing_payment_options'),
                ],
            ])
        ;

        Event::assertDispatched(RequestToEcom::class);
        Event::assertListening(RequestToEcom::class, RequestToEcomListener::class);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['company_name'] = null;

        $res = $this->putJson(route('api.v1.settings.update'), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.company_name')]),
            'company_name'
        );
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->putJson(route('api.v1.settings.update'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->putJson(route('api.v1.settings.update'), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['company_name', null, 'validation.required', ['attribute' => 'validation.attributes.company_name']],
            ['address', null, 'validation.required', ['attribute' => 'validation.attributes.address']],
            ['city', null, 'validation.required', ['attribute' => 'validation.attributes.city']],
            ['state_id', null, 'validation.required', ['attribute' => 'validation.attributes.state_id']],
            ['zip', null, 'validation.required', ['attribute' => 'validation.attributes.zip']],
            ['timezone', null, 'validation.required', ['attribute' => 'validation.attributes.timezone']],
            ['phone', null, 'validation.required', ['attribute' => 'validation.attributes.phone']],
            ['email', null, 'validation.required', ['attribute' => 'validation.attributes.email']],
            ['billing_phone', null, 'validation.required', ['attribute' => 'validation.attributes.billing_phone']],
            ['ecommerce_company_name', null, 'validation.required', ['attribute' => 'validation.attributes.ecommerce_company_name']],
            ['ecommerce_address', null, 'validation.required', ['attribute' => 'validation.attributes.ecommerce_address']],
            ['ecommerce_city', null, 'validation.required', ['attribute' => 'validation.attributes.ecommerce_city']],
            ['ecommerce_state_id', null, 'validation.required', ['attribute' => 'validation.attributes.ecommerce_state_id']],
            ['ecommerce_zip', null, 'validation.required', ['attribute' => 'validation.attributes.ecommerce_zip']],
            ['ecommerce_phone', null, 'validation.required', ['attribute' => 'validation.attributes.ecommerce_phone']],
            ['ecommerce_email', null, 'validation.required', ['attribute' => 'validation.attributes.ecommerce_email']],
            ['ecommerce_billing_phone', null, 'validation.required', ['attribute' => 'validation.attributes.ecommerce_billing_phone']],
        ];
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->putJson(route('api.v1.settings.update'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->putJson(route('api.v1.settings.update'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}

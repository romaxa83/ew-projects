<?php

namespace Tests\Feature\Mutations\FrontOffice\Companies;

use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\Mutations\FrontOffice\Companies\ShippingAddress\UpdateShippingAddressMutation;
use App\Listeners\Companies\SendDataToOnecListeners;
use App\Models\Companies\Company;
use App\Models\Locations\State;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\TestCase;

class UpdateShippingAddressMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = UpdateShippingAddressMutation::NAME;

    protected CompanyBuilder $companyBuilder;
    protected CompanyShippingAddressBuilder $companyShippingAddressBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->companyShippingAddressBuilder = resolve(CompanyShippingAddressBuilder::class);
    }

    /** @test */
    public function success_create(): void
    {
        Event::fake([CreateOrUpdateCompanyEvent::class]);

        $this->loginAsDealerWithRole();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $address = $this->companyShippingAddressBuilder->setCompany($company)->create();

        $this->assertCount(1, $company->shippingAddresses);

        $data = $this->data();
        $data['id'] = $address->id;

        $this->assertNotEquals($company->shippingAddresses[0]->name, data_get($data, 'shipping_address.name'));
        $this->assertNotEquals($company->shippingAddresses[0]->phone->getValue(), data_get($data, 'shipping_address.phone'));
        $this->assertNotEquals($company->shippingAddresses[0]->fax->getValue(), data_get($data, 'shipping_address.fax'));
        $this->assertNotEquals($company->shippingAddresses[0]->email->getValue(), data_get($data, 'shipping_address.email'));
        $this->assertNotEquals($company->shippingAddresses[0]->receiving_persona, data_get($data, 'shipping_address.receiving_persona'));
        $this->assertNotEquals($company->shippingAddresses[0]->country->country_code, data_get($data, 'shipping_address.country_code'));
        $this->assertNotEquals($company->shippingAddresses[0]->state_id, data_get($data, 'shipping_address.state_id'));
        $this->assertNotEquals($company->shippingAddresses[0]->address_line_1, data_get($data, 'shipping_address.address_line_1'));
        $this->assertNotEquals($company->shippingAddresses[0]->address_line_2, data_get($data, 'shipping_address.address_line_2'));
        $this->assertNotEquals($company->shippingAddresses[0]->zip, data_get($data, 'shipping_address.zip'));

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $address->id,
                        'name' => data_get($data, 'shipping_address.name'),
                        'active' => true,
                        'phone' => data_get($data, 'shipping_address.phone'),
                        'fax' => data_get($data, 'shipping_address.fax'),
                        'email' => data_get($data, 'shipping_address.email'),
                        'receiving_persona' => data_get($data, 'shipping_address.receiving_persona'),
                        'country' => [
                            'country_code' => data_get($data, 'shipping_address.country_code')
                        ],
                        'state' => [
                            'id' => data_get($data, 'shipping_address.state_id')
                        ],
                        'city' => data_get($data, 'shipping_address.city'),
                        'address_line_1' => data_get($data, 'shipping_address.address_line_1'),
                        'address_line_2' => data_get($data, 'shipping_address.address_line_2'),
                        'zip' => data_get($data, 'shipping_address.zip'),
                    ]
                ]
            ])
        ;

        Event::assertDispatched(function (CreateOrUpdateCompanyEvent $event) use ($company) {
            return $event->getCompany()->id === $company->id;
        });
        Event::assertListening(CreateOrUpdateCompanyEvent::class, SendDataToOnecListeners::class);
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $address = $this->companyShippingAddressBuilder->setCompany($company)->create();

        $data = $this->data();
        $data['id'] = $address->id;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])

            ->assertJson([
                'errors' => [
                    ['message' => "Unauthorized"]
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $address = $this->companyShippingAddressBuilder->setCompany($company)->create();
        $this->loginAsDealer();

        $data = $this->data();
        $data['id'] = $address->id;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])

            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }

    public function data(): array
    {
        $state_1 = State::with('country')->latest('id')->first();

        return [
            'shipping_address' => [
                'name' => $this->faker->company,
                'phone' => '38099990903',
                'fax' => '38099990904',
                'email' => $this->faker->unique()->safeEmail,
                'receiving_persona' => $this->faker->userName,
                'country_code' => $state_1->country->country_code,
                'state_id' => $state_1->id,
                'city' => $this->faker->city,
                'address_line_1' => $this->faker->streetName,
                'address_line_2' => $this->faker->streetName,
                'zip' => $this->faker->postcode,
            ]
        ];
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    shipping_address: {
                        name: "%s"
                        phone: "%s"
                        fax: "%s"
                        email: "%s"
                        receiving_persona: "%s"
                        country_code: "%s"
                        state_id: %s
                        city: "%s"
                        address_line_1: "%s"
                        address_line_2: "%s"
                        zip: "%s"
                    }
                ) {
                    id
                    name
                    active
                    phone
                    fax
                    email
                    receiving_persona
                    country {
                        country_code
                    }
                    state {
                        id
                    }
                    city
                    address_line_1
                    address_line_2
                    zip
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'shipping_address.name'),
            data_get($data, 'shipping_address.phone'),
            data_get($data, 'shipping_address.fax'),
            data_get($data, 'shipping_address.email'),
            data_get($data, 'shipping_address.receiving_persona'),
            data_get($data, 'shipping_address.country_code'),
            data_get($data, 'shipping_address.state_id'),
            data_get($data, 'shipping_address.city'),
            data_get($data, 'shipping_address.address_line_1'),
            data_get($data, 'shipping_address.address_line_2'),
            data_get($data, 'shipping_address.zip'),
        );
    }
}



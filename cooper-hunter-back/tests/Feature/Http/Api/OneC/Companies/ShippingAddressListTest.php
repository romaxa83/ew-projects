<?php

namespace Tests\Feature\Http\Api\OneC\Companies;

use App\Models\Companies\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\TestCase;

class ShippingAddressListTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected CompanyBuilder $companyBuilder;
    protected CompanyShippingAddressBuilder $addressBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->addressBuilder = resolve(CompanyShippingAddressBuilder::class);
    }

    /** @test */
    public function success_list(): void
    {
        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $addr_1 = $this->addressBuilder->setCompany($model)->create();
        $addr_2 = $this->addressBuilder->setCompany($model)->create();

        $this->getJson(route('1c.companies.shipping-addresses.list', ['guid' => $model->guid]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'id' => $addr_1->id,
                        'active' => $addr_1->active,
                        'name' => $addr_1->name,
                        'phone' => $addr_1->phone->getValue(),
                        'fax' => $addr_1->fax->getValue(),
                        'email' => $addr_1->email->getValue(),
                        'receiving_persona' => $addr_1->receiving_persona,
                        'country' => $addr_1->country->country_code,
                        'state' => $addr_1->state->short_name,
                        'city' => $addr_1->city,
                        'address_line_1' => $addr_1->address_line_1,
                        'address_line_2' => $addr_1->address_line_2,
                        'zip' => $addr_1->zip,
                    ],
                    [
                        'id' => $addr_2->id,
                        'active' => $addr_2->active,
                        'name' => $addr_2->name,
                        'phone' => $addr_2->phone->getValue(),
                        'fax' => $addr_2->fax->getValue(),
                        'email' => $addr_2->email->getValue(),
                        'receiving_persona' => $addr_2->receiving_persona,
                        'country' => $addr_2->country->country_code,
                        'state' => $addr_2->state->short_name,
                        'city' => $addr_2->city,
                        'address_line_1' => $addr_2->address_line_1,
                        'address_line_2' => $addr_2->address_line_2,
                        'zip' => $addr_2->zip,
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_list_empty(): void
    {
        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $this->getJson(route('1c.companies.shipping-addresses.list', ['guid' => $model->guid]))
            ->assertOk()
            ->assertJson([
                'data' => [],
            ])
            ->assertJsonCount(0, 'data')
        ;
    }
}



<?php

namespace Tests\Feature\Mutations\FrontOffice\Companies;

use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\Mutations\FrontOffice\Companies\ShippingAddress\DeleteShippingAddressMutation;
use App\Listeners\Companies\SendDataToOnecListeners;
use App\Models\Companies\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\TestCase;

class DeleteShippingAddressMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DeleteShippingAddressMutation::NAME;

    protected CompanyBuilder $companyBuilder;
    protected CompanyShippingAddressBuilder $companyShippingAddressBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->companyShippingAddressBuilder = resolve(CompanyShippingAddressBuilder::class);
    }

    /** @test */
    public function success_delete(): void
    {
        Event::fake([CreateOrUpdateCompanyEvent::class]);

        /** @var $company Company */
        $company = $this->companyBuilder->withContacts()->create();
        $this->loginAsDealerWithRole();

        $address_1 = $this->companyShippingAddressBuilder->setCompany($company)->create();
        $address_2 = $this->companyShippingAddressBuilder->setCompany($company)->create();

        $this->assertCount(2, $company->shippingAddresses);

        $this->postGraphQL([
            'query' => $this->getQueryStr($address_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ])
        ;

        $company->refresh();

        $this->assertCount(1, $company->shippingAddresses);

        Event::assertDispatched(function (CreateOrUpdateCompanyEvent $event) use ($company) {
            return $event->getCompany()->id === $company->id;
        });
        Event::assertListening(CreateOrUpdateCompanyEvent::class, SendDataToOnecListeners::class);
    }

    /** @test */
    public function fail_wrong_id(): void
    {
        $this->loginAsDealerWithRole();

        $this->postGraphQL([
            'query' => $this->getQueryStr(1)
        ])
            ->assertJson([
                'errors' => [
                    ["message" => "validation"]
                ]
            ])
        ;
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $address_1 = $this->companyShippingAddressBuilder->setCompany($company)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($address_1->id)
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
        $this->loginAsDealer();

        $address_1 = $this->companyShippingAddressBuilder->setCompany($company)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($address_1->id)
        ])

            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                )
            }',
            self::MUTATION,
            $id
        );
    }
}

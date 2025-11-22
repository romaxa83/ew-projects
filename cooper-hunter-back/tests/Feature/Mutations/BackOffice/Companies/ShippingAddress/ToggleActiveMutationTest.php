<?php

namespace Tests\Feature\Mutations\BackOffice\Companies\ShippingAddress;

use App\GraphQL\Mutations\BackOffice\Companies\ShippingAddress\ToggleActiveMutation;
use App\Models\Companies\ShippingAddress;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\TestCase;

class ToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ToggleActiveMutation::NAME;

    protected CompanyShippingAddressBuilder $addressBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->addressBuilder = resolve(CompanyShippingAddressBuilder::class);
    }

    /** @test */
    public function success_send(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model ShippingAddress */
        $model = $this->addressBuilder->create();

        $this->assertTrue($model->isActive());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'active' => false,
                    ],
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
                ) {
                    id
                    active
                }
            }',
            self::MUTATION,
            $id
        );
    }
}


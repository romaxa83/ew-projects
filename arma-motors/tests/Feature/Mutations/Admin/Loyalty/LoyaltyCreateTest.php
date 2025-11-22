<?php

namespace Tests\Feature\Mutations\Admin\Loyalty;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\Brand;
use App\Models\User\Loyalty\Loyalty;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class LoyaltyCreateTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::LOYALTY_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::find(1);
        $data = $this->data(brandId: $brand->id);
        $data['age'] = '5+';

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.loyaltyCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('discount', $responseData);
        $this->assertArrayHasKey('age', $responseData);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('id', $responseData['brand']);
        $this->assertArrayHasKey('name', $responseData['brand']);

        $this->assertTrue($responseData['active']);
        $this->assertEquals($data['type'], $responseData['type']);
        $this->assertEquals($data['discount'], $responseData['discount']);
        $this->assertEquals($data['age'], $responseData['age']);
        $this->assertEquals($brand->name, $responseData['brand']['name']);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::LOYALTY_CREATE)
            ->create();

        $brand = Brand::find(1);
        $data = $this->data(brandId: $brand->id);
        $data['age'] = '5+';

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::USER_CAR_LIST)
            ->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::find(1);
        $data = $this->data(brandId: $brand->id);
        $data['age'] = '5+';

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function data(
        bool $active = true,
        $type = Loyalty::TYPE_SERVICE,
        $discount = 5.0,
        $brandId = 1
    ): array
    {

        $activeString = $active == true ? 'true' : 'false' ;

        return [
            'active' => $activeString,
            'type' => $type,
            'discount' => $discount,
            'brandId' => $brandId
        ];
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                loyaltyCreate(input:{
                    active: %s,
                    type: %s,
                    discount: %f,
                    brandId: %s,
                    age: "%s",
                }) {
                    id
                    active
                    type
                    discount
                    age
                    brand {
                        id
                        name
                    }
                }
            }',
            $data['active'],
            $data['type'],
            $data['discount'],
            $data['brandId'],
            $data['age'],
        );
    }
}



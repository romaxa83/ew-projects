<?php

namespace Tests\Feature\Mutations\Admin\Loyalty;

use App\Exceptions\ErrorsCode;
use App\Models\User\Loyalty\Loyalty;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class LoyaltyEditTest extends TestCase
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
            ->createRoleWithPerm(Permissions::LOYALTY_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Loyalty::find(1);
        $data = [
            'id' => $model->id,
            'discount' => 20.0,
            'active' => 'false'
        ];

        $this->assertTrue($model->active);
        $this->assertNotEquals($model->discount_float, $data['discount']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.loyaltyEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('discount', $responseData);

        $model->refresh();

        $this->assertFalse($model->active);
        $this->assertEquals($model->discount_float, $data['discount']);
    }

    /** @test */
    public function success_only_discount()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::LOYALTY_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Loyalty::find(1);
        $data = [
            'id' => $model->id,
            'discount' => 20.0,
        ];

        $this->assertTrue($model->active);
        $this->assertNotEquals($model->discount_float, $data['discount']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyDiscount($data)]);

        $responseData = $response->json('data.loyaltyEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('discount', $responseData);

        $model->refresh();

        $this->assertTrue($model->active);
        $this->assertEquals($model->discount_float, $data['discount']);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::LOYALTY_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => 999,
            'discount' => 20.0,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyDiscount($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::LOYALTY_EDIT)
            ->create();

        $data = ['id' => 1, 'discount' => 20.0,];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyDiscount($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = ['id' => 1, 'discount' => 20.0,];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyDiscount($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                loyaltyEdit(input:{
                    id: "%s",
                    active: %s,
                    discount: %f
                }) {
                    active
                    discount
                }
            }',
            $data['id'],
            $data['active'],
            $data['discount'],
        );
    }

    private function getQueryStrOnlyDiscount(array $data): string
    {
        return sprintf('
            mutation {
                loyaltyEdit(input:{
                    id: "%s",
                    discount: %f
                }) {
                    active
                    discount
                }
            }',
            $data['id'],
            $data['discount'],
        );
    }
}






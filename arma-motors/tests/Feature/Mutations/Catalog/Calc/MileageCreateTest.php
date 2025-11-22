<?php

namespace Tests\Feature\Mutations\Catalog\Calc;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Calc\Mileage;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class MileageCreateTest extends TestCase
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
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'value' => 10000
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.mileageCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('value', $responseData);

        $this->assertEquals($data['value'], $responseData['value']);

        $model = Mileage::where('id', $responseData['id'])->first();

        $this->assertNotNull($model);
        $this->assertEquals($model->value, $responseData['value']);
    }

    /** @test */
    public function success_only_required_field()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'value' => 10000
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequired($data)])
            ->assertOk();

        $responseData = $response->json('data.mileageCreate');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('value', $responseData);

        $model = Mileage::where('id', $responseData['id'])->first();

        $this->assertNotNull($model);
        $this->assertEquals($model->value, $responseData['value']);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequired(['value' => 10000])]);

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

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequired(['value' => 10000])]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                mileageCreate(input:{
                    active: true,
                    value: %d,
                }) {
                    id
                    active
                    value
                }
            }',
            $data['value'],
        );
    }

    private function getQueryStrOnlyRequired(array $data): string
    {
        return sprintf('
            mutation {
                mileageCreate(input:{
                    value: %d
                }) {
                    id
                    active
                    value
                }
            }',
            $data['value'],
        );
    }
}

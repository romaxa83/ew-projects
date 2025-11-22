<?php

namespace Tests\Feature\Queries\User;

use App\Exceptions\ErrorsCode;
use App\Models\User\Loyalty\Loyalty;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class LoyaltyOneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success_by_id()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::LOYALTY_GET)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Loyalty::find(1);

        $response = $this->graphQL($this->getQueryStr($model->id));

        $responseData = $response->json('data.loyalty');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('age', $responseData);
        $this->assertArrayHasKey('discount', $responseData);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('id', $responseData['brand']);
        $this->assertArrayHasKey('name', $responseData['brand']);

        $this->assertEquals($model->id, $responseData['id']);
        $this->assertEquals($model->active, $responseData['active']);
        $this->assertEquals($model->brand->id, $responseData['brand']['id']);
        $this->assertNotEquals($model->discount, $responseData['discount']);
        $this->assertEquals($model->discount_float, $responseData['discount']);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr(999));
        $this->assertNull($response->json('data.loyalty'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::LOYALTY_GET)
            ->create();

        $model = Loyalty::find(1);

        $response = $this->graphQL($this->getQueryStr($model->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::LOYALTY_LIST)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Loyalty::find(1);

        $response = $this->graphQL($this->getQueryStr($model->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(string $id): string
    {
        return  sprintf('{
            loyalty (id: %s) {
                id
                active
                type
                age
                discount
                brand {
                    id
                    name
                }
               }
            }',
            $id
        );
    }
}

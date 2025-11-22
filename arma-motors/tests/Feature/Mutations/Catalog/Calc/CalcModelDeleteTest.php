<?php

namespace Tests\Feature\Mutations\Catalog\Calc;

use App\Exceptions\ErrorsCode;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\CalcModelBuilder;

class CalcModelDeleteTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CalcModelBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::CALC_CATALOG_DELETE])
            ->create();
        $this->loginAsAdmin($admin);

        $calcModelBuilder = $this->calcModelBuilder();
        $model = $calcModelBuilder->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($model->id)]);

        $this->assertTrue($response->json('data.calcModelDelete.status'));
        $this->assertEquals($response->json('data.calcModelDelete.message'), __('message.calc model deleted'));
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::CALC_CATALOG_DELETE])
            ->create();
        $this->loginAsAdmin($admin);

        $calcModelBuilder = $this->calcModelBuilder();
        $model = $calcModelBuilder->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr(999)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::CALC_CATALOG_DELETE])
            ->create();


        $calcModelBuilder = $this->calcModelBuilder();
        $model = $calcModelBuilder->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr(999)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::CALC_CATALOG_CREATE])
            ->create();
        $this->loginAsAdmin($admin);

        $calcModelBuilder = $this->calcModelBuilder();
        $model = $calcModelBuilder->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($model->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr($id): string
    {
        return sprintf('
            mutation {
                calcModelDelete(id: %d) {
                    status
                    message
                }
            }',
            $id
        );
    }
}



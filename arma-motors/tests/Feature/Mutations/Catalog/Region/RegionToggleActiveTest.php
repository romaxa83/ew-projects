<?php

namespace Tests\Feature\Mutations\Catalog\Region;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Region\Region;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class RegionToggleActiveTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function toggle_success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_REGION_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Region::orderBy(\DB::raw('RAND()'))->first();

        $this->assertTrue($model->active);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($model->id)])
            ->assertOk();

        $responseData = $response->json('data.regionToggleActive');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertFalse($responseData['active']);

        $model->refresh();
        $this->assertFalse($model->active);

        $this->postGraphQL(['query' => $this->getQueryStr($model->id)]);

        $model->refresh();
        $this->assertTrue($model->active);
    }

    /** @test */
    public function not_found_model()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_REGION_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->postGraphQL(['query' => $this->getQueryStr('999')])
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function not_auth()
    {
        $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_REGION_EDIT)
            ->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr(1)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::USER_LIST)
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->postGraphQL(['query' => $this->getQueryStr(1)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(string $id): string
    {
        return sprintf('
            mutation {
                regionToggleActive(id: "%s") {
                    id
                    active
                    sort
                }
            }',
            $id,
        );
    }
}


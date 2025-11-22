<?php

namespace Tests\Feature\Mutations\Catalog\Car;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\DriveUnit;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class DriveUnitEditTest extends TestCase
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
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = DriveUnit::where('id', 1)->first();

        $data = $this->data($model->id);

        $this->assertTrue($model->active);
        $this->assertNotEquals($model->sort, $data['sort']);
        $this->assertNotEquals($model->name, $data['name']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.driveUnitEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $model->refresh();

        $this->assertFalse($model->active);
        $this->assertEquals($model->sort, $data['sort']);
        $this->assertEquals($model->name, $data['name']);
    }

    /** @test */
    public function success_only_sort()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = DriveUnit::where('id', 1)->first();

        $data = [
            'id' => $model->id,
            'sort' => 3
        ];

        $this->assertTrue($model->active);
        $this->assertNotEquals($model->sort, $data['sort']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlySort($data)])
            ->assertOk();

        $responseData = $response->json('data.driveUnitEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertEquals($model->id, $responseData['id']);

        $model->refresh();

        $this->assertTrue($model->active);
        $this->assertEquals($model->sort, $data['sort']);
    }

    /** @test */
    public function success_only_name()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = DriveUnit::where('id', 1)->first();

        $data = [
            'id' => $model->id,
            'name' => 'rrrr'
        ];

        $this->assertNotEquals($model->name, $data['name']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyVolume($data)])
            ->assertOk();

        $responseData = $response->json('data.driveUnitEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertEquals($model->id, $responseData['id']);

        $model->refresh();

        $this->assertTrue($model->active);
        $this->assertEquals($model->name, $data['name']);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => 999,
            'sort' => 3
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlySort($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();

        $data = ['id' => 1, 'sort' => 3];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlySort($data)]);

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

        $data = ['id' => 1, 'sort' => 3];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlySort($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function data(
        $id
    ): array
    {
        return [
            'id' => $id,
            'sort' => 3,
            'name' => '4WD'
        ];
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                driveUnitEdit(input:{
                    id: "%s",
                    active: false,
                    sort: %d,
                    name: "%s",
                }) {
                    active
                    sort
                    name
                }
            }',
            $data['id'],
            $data['sort'],
            $data['name'],
        );
    }

    private function getQueryStrOnlySort(array $data): string
    {
        return sprintf('
            mutation {
                driveUnitEdit(input:{
                    id: "%s",
                    sort: %d,
                }) {
                    id
                    active
                    sort
                    name
                }
            }',
            $data['id'],
            $data['sort'],
        );
    }

    private function getQueryStrOnlyVolume(array $data): string
    {
        return sprintf('
            mutation {
                driveUnitEdit(input:{
                    id: "%s",
                    name: "%s",
                }) {

                    id
                    active
                    sort
                    name
                }
            }',
            $data['id'],
            $data['name'],
        );
    }
}





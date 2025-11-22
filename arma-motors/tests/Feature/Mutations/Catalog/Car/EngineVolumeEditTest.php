<?php

namespace Tests\Feature\Mutations\Catalog\Car;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\EngineVolume;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class EngineVolumeEditTest extends TestCase
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

        $model = EngineVolume::where('id', 1)->first();

        $data = $this->data($model->id);

        $this->assertTrue($model->active);
        $this->assertNotEquals($model->sort, $data['sort']);
        $this->assertNotEquals($model->volume->getValue(), $data['volume']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.engineVolumeEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('volume', $responseData);

        $model->refresh();

        $this->assertFalse($model->active);
        $this->assertEquals($model->sort, $data['sort']);
        $this->assertEquals($model->volume->getValue(), $data['volume']);
    }

    /** @test */
    public function success_only_sort()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = EngineVolume::where('id', 1)->first();

        $data = [
            'id' => $model->id,
            'sort' => 3
        ];

        $this->assertTrue($model->active);
        $this->assertNotEquals($model->sort, $data['sort']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlySort($data)])
            ->assertOk();

        $responseData = $response->json('data.engineVolumeEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertEquals($model->id, $responseData['id']);

        $model->refresh();

        $this->assertTrue($model->active);
        $this->assertEquals($model->sort, $data['sort']);
    }

    /** @test */
    public function success_only_volume()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = EngineVolume::where('id', 1)->first();

        $data = [
            'id' => $model->id,
            'volume' => 3.7
        ];

        $this->assertNotEquals($model->volume->getValue(), $data['volume']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyVolume($data)])
            ->assertOk();

        $responseData = $response->json('data.engineVolumeEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('volume', $responseData);
        $this->assertEquals($model->id, $responseData['id']);

        $model->refresh();

        $this->assertTrue($model->active);
        $this->assertEquals($model->volume->getValue(), $data['volume']);
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
            'volume' => 5.8
        ];
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                engineVolumeEdit(input:{
                    id: "%s",
                    active: false,
                    sort: %d,
                    volume: %e,
                }) {
                    active
                    sort
                    volume
                }
            }',
            $data['id'],
            $data['sort'],
            $data['volume'],
        );
    }

    private function getQueryStrOnlySort(array $data): string
    {
        return sprintf('
            mutation {
                engineVolumeEdit(input:{
                    id: "%s",
                    sort: %d,
                }) {
                    id
                    active
                    sort
                    volume
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
                engineVolumeEdit(input:{
                    id: "%s",
                    volume: %e,
                }) {

                    id
                    active
                    sort
                    volume
                }
            }',
            $data['id'],
            $data['volume'],
        );
    }
}





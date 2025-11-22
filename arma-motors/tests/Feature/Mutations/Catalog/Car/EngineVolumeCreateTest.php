<?php

namespace Tests\Feature\Mutations\Catalog\Car;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\EngineVolume;
use App\Types\Permissions;
use App\ValueObjects\Volume;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class EngineVolumeCreateTest extends TestCase
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
            'sort' => 3,
            'volume' => 2.5
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.engineVolumeCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('volume', $responseData);

        $this->assertEquals($data['sort'], $responseData['sort']);
        $this->assertEquals($data['volume'], $responseData['volume']);

        $model = EngineVolume::where('id', $responseData['id'])->first();

        $this->assertNotNull($model);

        $this->assertTrue($model->volume instanceof Volume);
        $this->assertEquals($model->volume->getValue(), $data['volume']);
    }

    /** @test */
    public function volume_as_string()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'sort' => 3,
            'volume' => '2.5'
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.engineVolumeCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('volume', $responseData);

        $this->assertEquals($data['sort'], $responseData['sort']);
        $this->assertEquals($data['volume'], $responseData['volume']);

        $model = EngineVolume::where('id', $responseData['id'])->first();

        $this->assertNotNull($model);

        $this->assertTrue($model->volume instanceof Volume);
        $this->assertEquals($model->volume->getValue(), $data['volume']);
    }

    /** @test */
    public function only_volume()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = 2.8;

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyVolume($data)])
            ->assertOk();

        $responseData = $response->json('data.engineVolumeCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('volume', $responseData);

        $this->assertEquals($data, $responseData['volume']);
        $this->assertNotNull($responseData['sort']);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyVolume(1.8)]);

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

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyVolume(1.8)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                engineVolumeCreate(input:{
                    active: true,
                    sort: %d,
                    volume: %e,
                }) {
                    id
                    active
                    sort
                    volume
                }
            }',
            $data['sort'],
            $data['volume'],
        );
    }

    private function getQueryStrOnlyVolume($volume): string
    {
        return sprintf('
            mutation {
                engineVolumeCreate(input:{
                    volume: %e,
                }) {
                    id
                    active
                    sort
                    volume
                }
            }',
            $volume,
        );
    }
}





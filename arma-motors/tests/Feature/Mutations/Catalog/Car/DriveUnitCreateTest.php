<?php

namespace Tests\Feature\Mutations\Catalog\Car;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\DriveUnit;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class DriveUnitCreateTest extends TestCase
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
            'name' => '4WD'
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.driveUnitCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals($data['sort'], $responseData['sort']);
        $this->assertEquals($data['name'], $responseData['name']);

        $model = DriveUnit::where('id', $responseData['id'])->first();

        $this->assertNotNull($model);

        $this->assertEquals($model->name, $data['name']);
    }

    /** @test */
    public function only_name()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = '4WD';

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyVolume($data)])
            ->assertOk();

        $responseData = $response->json('data.driveUnitCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals($data, $responseData['name']);
        $this->assertNotNull($responseData['sort']);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyVolume('4WD')]);

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

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyVolume('4WD')]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                driveUnitCreate(input:{
                    active: true,
                    sort: %d,
                    name: "%s",
                }) {
                    id
                    active
                    sort
                    name
                }
            }',
            $data['sort'],
            $data['name'],
        );
    }

    private function getQueryStrOnlyVolume($value): string
    {
        return sprintf('
            mutation {
                driveUnitCreate(input:{
                    name: "%s",
                }) {
                    id
                    active
                    sort
                    name
                }
            }',
            $value,
        );
    }
}






<?php

namespace Tests\Feature\Mutations\Catalog\Service;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Service\DriverAge;
use App\Models\Catalogs\Service\Privileges;
use App\Models\Catalogs\Service\Service;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class DriverAgeEditTest extends TestCase
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
            ->createRoleWithPerm(Permissions::CATALOG_OTHER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = DriverAge::where('id', 1)->first();

        $data = $this->data($model->id);

        $this->assertTrue($model->active);
        $this->assertNotEquals($model->sort, $data['sort']);
        $this->assertNotEquals($model->current->name, $data[$model->current->lang]['name']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.driverAgeEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);

        $model->refresh();

        $this->assertFalse($model->active);
        $this->assertEquals($model->sort, $data['sort']);
        $this->assertEquals($model->current->name, $data[$model->current->lang]['name']);
    }

    /** @test */
    public function success_only_sort()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_OTHER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = DriverAge::where('id', 1)->first();

        $data = [
            'id' => $model->id,
            'sort' => 3
        ];

        $this->assertTrue($model->active);
        $this->assertNotEquals($model->sort, $data['sort']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlySort($data)])
            ->assertOk();

        $responseData = $response->json('data.driverAgeEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertEquals($model->id, $responseData['id']);
        $this->assertEquals($model->current->name, $responseData['current']['name']);

        $model->refresh();

        $this->assertTrue($model->active);
        $this->assertEquals($model->sort, $data['sort']);
    }

    /** @test */
    public function success_only_translation()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_OTHER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = DriverAge::where('id', 1)->first();

        $data = [
            'id' => $model->id,
            'ru' => [
                'lang' => 'ru',
                'name' => 'some privileges ru',
            ],
            'uk' => [
                'lang' => 'uk',
                'name' => 'some privileges uk',
            ],
        ];

        $this->assertNotEquals($model->current->name, $data[$model->current->lang]['name']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyTranslation($data)])
            ->assertOk();

        $responseData = $response->json('data.driverAgeEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);

        $model->refresh();

        $this->assertEquals($model->current->name, $responseData['current']['name']);
        $this->assertEquals($model->current->name, $data[$model->current->lang]['name']);
    }

    /** @test */
    public function not_found_service()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_OTHER_EDIT)
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
            ->createRoleWithPerm(Permissions::CATALOG_OTHER_EDIT)
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
            'ru' => [
                'lang' => 'ru',
                'name' => 'driver age ru',
            ],
            'uk' => [
                'lang' => 'uk',
                'name' => 'driver age uk',
            ],
        ];
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                driverAgeEdit(input:{
                    id: "%s",
                    active: false,
                    sort: %d,
                    translations: [
                        {lang: "%s", name: "%s"}
                        {lang: "%s", name: "%s"}
                    ]
                }) {
                    active
                    sort
                    current {
                        lang
                        name
                    }
                }
            }',
            $data['id'],
            $data['sort'],
            $data['ru']['lang'],
            $data['ru']['name'],
            $data['uk']['lang'],
            $data['uk']['name'],
        );
    }

    private function getQueryStrOnlyTranslation(array $data): string
    {
        return sprintf('
            mutation {
                driverAgeEdit(input:{
                    id: "%s",
                    translations: [
                        {lang: "%s", name: "%s"}
                        {lang: "%s", name: "%s"}
                    ]
                }) {
                    active
                    sort
                    current {
                        lang
                        name
                    }
                }
            }',
            $data['id'],
            $data['ru']['lang'],
            $data['ru']['name'],
            $data['uk']['lang'],
            $data['uk']['name'],
        );
    }

    private function getQueryStrOnlySort(array $data): string
    {
        return sprintf('
            mutation {
                driverAgeEdit(input:{
                    id: "%s",
                    sort: %d,
                }) {
                    id
                    active
                    sort
                    current {
                        lang
                        name
                    }
                }
            }',
            $data['id'],
            $data['sort'],
        );
    }
}





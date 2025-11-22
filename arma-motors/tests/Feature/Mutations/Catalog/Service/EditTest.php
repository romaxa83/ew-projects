<?php

namespace Tests\Feature\Mutations\Catalog\Service;

use App\Events\ChangeHashEvent;
use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Service\Service;
use App\Models\Hash;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class EditTest extends TestCase
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
        \Event::fake([ChangeHashEvent::class]);

        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_SERVICE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::where('id', 1)->first();
        $service2 = Service::where('id', 2)->first();

        $data = $this->data($service->id, $service2->id);

        $this->assertTrue($service->active);
        $this->assertNotEquals($service->sort, $data['sort']);
        $this->assertNotEquals($service->time_step, $data['timeStep']);
        $this->assertNotEquals($service->parent_id, $data['parentId']);
        $this->assertNotEquals($service->current->name, $data[$service->current->lang]['name']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.serviceEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('timeStep', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('parent', $responseData);
        $this->assertArrayHasKey('id', $responseData['parent']);

        $service->refresh();

        $this->assertFalse($service->active);
        $this->assertEquals($service->sort, $data['sort']);
        $this->assertEquals($service->time_step, $data['timeStep']);
        $this->assertEquals($service->parent_id, $data['parentId']);
        $this->assertEquals($service->current->name, $data[$service->current->lang]['name']);

        \Event::assertDispatched(function (ChangeHashEvent $event){
            return $event->alias == Hash::ALIAS_SERVICE;
        });
    }

    /** @test */
    public function success_only_sort()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_SERVICE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::where('id', 1)->first();

        $data = [
            'id' => $service->id,
            'sort' => 3
        ];

        $this->assertTrue($service->active);
        $this->assertNotEquals($service->sort, $data['sort']);
        $this->assertEquals($service->time_step, 0);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlySort($data)])
            ->assertOk();

        $responseData = $response->json('data.serviceEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('timeStep', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('parent', $responseData);
        $this->assertArrayHasKey('childs', $responseData);
        $this->assertArrayHasKey('id', $responseData['childs'][0]);
        $this->assertNull($responseData['parent']);
        $this->assertEquals($service->id, $responseData['id']);
        $this->assertEquals($service->current->name, $responseData['current']['name']);

        $service->refresh();

        $this->assertTrue($service->active);
        $this->assertEquals($service->sort, $data['sort']);
        $this->assertEquals($service->time_step, 0);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_SERVICE_EDIT)
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
            ->createRoleWithPerm(Permissions::CATALOG_SERVICE_EDIT)
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
        $id,
        $parentId = null
    ): array
    {
        return [
            'id' => $id,
            'sort' => 3,
            'timeStep' => 45,
            'parentId' => $parentId,
            'ru' => [
                'lang' => 'ru',
                'name' => 'some service ru',
            ],
            'uk' => [
                'lang' => 'uk',
                'name' => 'some service uk',
            ],
        ];
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                serviceEdit(input:{
                    id: "%s",
                    active: false,
                    sort: %d,
                    timeStep: %d,
                    parentId: "%s",
                    translations: [
                        {lang: "%s", name: "%s"}
                        {lang: "%s", name: "%s"}
                    ]
                }) {
                    active
                    alias
                    sort
                    timeStep
                    current {
                        lang
                        name
                    }
                    parent {
                        id
                    }
                    childs {
                        id
                    }
                }
            }',
            $data['id'],
            $data['sort'],
            $data['timeStep'],
            $data['parentId'],
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
                serviceEdit(input:{
                    id: "%s",
                    sort: %d,
                }) {
                    id
                    active
                    sort
                    timeStep
                    current {
                        lang
                        name
                    }
                    childs {
                        id
                    }
                    parent {
                        id
                    }

                }
            }',
            $data['id'],
            $data['sort'],
        );
    }
}




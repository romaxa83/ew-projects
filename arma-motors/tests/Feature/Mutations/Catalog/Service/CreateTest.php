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

class CreateTest extends TestCase
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
            ->createRoleWithPerm(Permissions::CATALOG_SERVICE_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::orderBy(\DB::raw('RAND()'))->first();
        $countChilds = $service->childs->count();

        $data = $this->data($service->id);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.serviceCreate');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('alias', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('timeStep', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('parent', $responseData);
        $this->assertArrayHasKey('id', $responseData['parent']);
        $this->assertArrayHasKey('childs', $responseData);

        $this->assertEquals($responseData['parent']['id'], $service->id);
        $this->assertEquals($data['alias'], $responseData['alias']);
        $this->assertEquals($data['sort'], $responseData['sort']);
        $this->assertEquals($data['timeStep'], $responseData['timeStep']);
        $this->assertEquals($data[$responseData['current']['lang']]['name'], $responseData['current']['name']);

        $service->refresh();
        $this->assertEquals($service->childs->count(), $countChilds +1);

        \Event::assertDispatched(function (ChangeHashEvent $event){
            return $event->alias == Hash::ALIAS_SERVICE;
        });

    }

    /** @test */
    public function success_only_required_field()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_SERVICE_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'alias' => 'some_alias_1',
            'ru' => [
                'lang' => 'ru',
                'name' => 'some service ru',
            ],
            'uk' => [
                'lang' => 'uk',
                'name' => 'some service uk',
            ],
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequired($data)])
            ->assertOk();

        $responseData = $response->json('data.serviceCreate');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('alias', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('timeStep', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('parent', $responseData);
        $this->assertArrayHasKey('childs', $responseData);
        $this->assertArrayHasKey('icon', $responseData);
        $this->assertArrayHasKey('forGuest', $responseData);

        $this->assertEmpty($responseData['childs']);
        $this->assertEquals(0, $responseData['timeStep']);
        $this->assertTrue($responseData['active']);
        $this->assertNull($responseData['parent']);
        $this->assertNull($responseData['icon']);
        $this->assertFalse($responseData['forGuest']);
        $this->assertEquals($data['alias'], $responseData['alias']);
        $this->assertEquals($data[$responseData['current']['lang']]['name'], $responseData['current']['name']);
    }

    /** @test */
    public function fail_uniq()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_SERVICE_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $service = Service::orderBy(\DB::raw('RAND()'))->first();

        $data = $this->data($service->id, $service->alias);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function fail_not_exists_service()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_SERVICE_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = $this->data(222);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_SERVICE_CREATE)
            ->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($this->data())]);

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

        $response = $this->postGraphQL(['query' => $this->getQueryStr($this->data())]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function data(
        $parentId = 1,
        $alias = 'some_alias'
    ): array
    {
        return [
            'alias' => $alias,
            'parentId' => $parentId,
            'sort' => 3,
            'timeStep' => 30,
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
                serviceCreate(input:{
                    alias: "%s",
                    active: true,
                    sort: %d,
                    timeStep: %d,
                    parentId: %d,
                    translations: [
                        {lang: "%s", name: "%s"}
                        {lang: "%s", name: "%s"}
                    ]
                }) {
                    active
                    alias
                    sort
                    timeStep
                    icon
                    forGuest
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
            $data['alias'],
            $data['sort'],
            $data['timeStep'],
            $data['parentId'],
            $data['ru']['lang'],
            $data['ru']['name'],
            $data['uk']['lang'],
            $data['uk']['name'],
        );
    }

    private function getQueryStrOnlyRequired(array $data): string
    {
        return sprintf('
            mutation {
                serviceCreate(input:{
                    alias: "%s",
                    translations: [
                        {lang: "%s", name: "%s"}
                        {lang: "%s", name: "%s"}
                    ]
                }) {
                    active
                    alias
                    sort
                    timeStep
                    icon
                    forGuest
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
            $data['alias'],
            $data['ru']['lang'],
            $data['ru']['name'],
            $data['uk']['lang'],
            $data['uk']['name'],
        );
    }
}



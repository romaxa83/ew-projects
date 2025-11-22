<?php

namespace Tests\Feature\Mutations\Catalog\Calc;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Calc\Work;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class WorkCreateTest extends TestCase
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

        $data = $this->data();
        $data['sort'] = 5;

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.workCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);

        $this->assertEquals($data['sort'], $responseData['sort']);
        $this->assertEquals($data[$responseData['current']['lang']]['name'], $responseData['current']['name']);

        $model = Work::where('id', $responseData['id'])->first();

        $this->assertNotNull($model);
        $this->assertEquals($model->current->name, $responseData['current']['name']);
    }

    /** @test */
    public function success_only_required_field()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = $this->data();

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequired($data)])
            ->assertOk();

        $responseData = $response->json('data.workCreate');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);

        $model = Work::where('id', $responseData['id'])->first();

        $this->assertNotNull($model);
        $this->assertEquals($model->current->name, $responseData['current']['name']);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequired($this->data())]);

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

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequired($this->data())]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function data(): array
    {
        return [
            'ru' => [
                'lang' => 'ru',
                'name' => 'some workCreate ru',
            ],
            'uk' => [
                'lang' => 'uk',
                'name' => 'some workCreate uk',
            ],
        ];
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                workCreate(input:{
                    active: true,
                    sort: %d,
                    translations: [
                        {lang: "%s", name: "%s"}
                        {lang: "%s", name: "%s"}
                    ]
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
            $data['sort'],
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
                workCreate(input:{
                    translations: [
                        {lang: "%s", name: "%s"}
                        {lang: "%s", name: "%s"}
                    ]
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
            $data['ru']['lang'],
            $data['ru']['name'],
            $data['uk']['lang'],
            $data['uk']['name'],
        );
    }
}

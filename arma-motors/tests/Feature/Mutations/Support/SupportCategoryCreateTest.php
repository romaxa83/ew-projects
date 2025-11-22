<?php

namespace Tests\Feature\Mutations\Support;

use App\Exceptions\ErrorsCode;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class SupportCategoryCreateTest extends TestCase
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
            ->createRoleWithPerm(Permissions::SUPPORT_CATEGORY_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = $this->data();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.supportCategoryCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);

        $this->assertEquals($data['sort'], $responseData['sort']);
        $this->assertEquals($data[$responseData['current']['lang']]['name'], $responseData['current']['name']);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::SUPPORT_CATEGORY_CREATE)
            ->create();

        $data = $this->data();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::USER_CAR_LIST)
            ->create();
        $this->loginAsAdmin($admin);

        $data = $this->data();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function data(
        int $sort = 1,
        bool $active = true
    ): array
    {

        $activeString = $active == true ? 'true' : 'false' ;

        return [
            'sort' => $sort,
            'active' => $activeString,
            'ru' => [
                'lang' => 'ru',
                'name' => 'create category ru',
            ],
            'uk' => [
                'lang' => 'uk',
                'name' => 'create category uk',
            ],
        ];
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                supportCategoryCreate(input:{
                    sort: %d,
                    active: %s,
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
            $data['active'],
            $data['ru']['lang'],
            $data['ru']['name'],
            $data['uk']['lang'],
            $data['uk']['name'],
        );
    }
}



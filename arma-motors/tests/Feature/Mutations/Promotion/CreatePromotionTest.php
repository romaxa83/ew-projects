<?php

namespace Tests\Feature\Mutations\Promotion;

use App\Exceptions\ErrorsCode;
use App\Models\Dealership\Department;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class CreatePromotionTest extends TestCase
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
            ->createRoleWithPerm(Permissions::PROMOTION_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $department = Department::orderBy(\DB::raw('RAND()'))->first();
        $data = $this->data(departmentId:$department->id);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.promotionCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('link', $responseData);
        $this->assertArrayHasKey('startAt', $responseData);
        $this->assertArrayHasKey('finishAt', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('text', $responseData['current']);
        $this->assertArrayHasKey('department', $responseData);
        $this->assertArrayHasKey('id', $responseData['department']);
        $this->assertArrayHasKey('current', $responseData['department']);
        $this->assertArrayHasKey('name', $responseData['department']['current']);

        $this->assertEquals($data['sort'], $responseData['sort']);
        $this->assertEquals($data['type'], $responseData['type']);
        $this->assertEquals($data['link'], $responseData['link']);
        $this->assertEquals($department->id, $responseData['department']['id']);
        $this->assertEquals($data[$responseData['current']['lang']]['name'], $responseData['current']['name']);
        $this->assertEquals($data[$responseData['current']['lang']]['text'], $responseData['current']['text']);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::PROMOTION_CREATE)
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
        bool $active = true,
        $departmentId = '1',
        $type = 'common',
        $link = 'some link',
    ): array
    {
        $activeString = $active == true ? 'true' : 'false' ;

        return [
            'sort' => $sort,
            'active' => $activeString,
            'type' => $type,
            'departmentId' => $departmentId,
            'link' => $link,
            'startAt' => "1624350625",
            'finishAt' => "1624350625",
            'ru' => [
                'lang' => 'ru',
                'name' => 'promotion name ru',
                'text' => 'promotion text ru'
            ],
            'uk' => [
                'lang' => 'uk',
                'name' => 'promotion name uk',
                'text' => 'promotion text uk'
            ],
        ];
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                promotionCreate(input:{
                    sort: %d,
                    active: %s,
                    type: %s,
                    departmentId: %s,
                    link: "%s",
                    startAt: "%s",
                    finishAt: "%s",
                    translations: [
                        {lang: "%s", name: "%s", text: "%s"}
                        {lang: "%s", name: "%s", , text: "%s"}
                    ]
                }) {
                    id
                    active
                    sort
                    type
                    link
                    startAt
                    finishAt
                    department {
                        id
                        current {
                            name
                        }
                    }
                    current {
                        lang
                        name
                        text
                    }
                }
            }',
            $data['sort'],
            $data['active'],
            $data['type'],
            $data['departmentId'],
            $data['link'],
            $data['startAt'],
            $data['finishAt'],
            $data['ru']['lang'],
            $data['ru']['name'],
            $data['ru']['text'],
            $data['uk']['lang'],
            $data['uk']['name'],
            $data['uk']['text'],
        );
    }
}



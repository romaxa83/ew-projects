<?php

namespace Tests\Feature\Mutations\Promotion;

use App\Exceptions\ErrorsCode;
use App\Models\Dealership\Department;
use App\Models\Promotion\Promotion;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\PromotionBuilder;

class EditPromotionTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use PromotionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::PROMOTION_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $department = Department::find(1);
        $promotion = $this->promotionBuilder()
            ->setDepartmentId($department->id)
            ->setType(Promotion::TYPE_COMMON)
            ->create();

        $data = $this->data(departmentId:2, type:Promotion::TYPE_INDIVIDUAL);
        $data['id'] = $promotion->id;

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.promotionEdit');

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

        $promotion->refresh();

        $this->assertEquals($promotion->department_id, 2);
        $this->assertEquals($promotion->type, Promotion::TYPE_INDIVIDUAL);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::PROMOTION_CREATE)
            ->create();

        $promotion = $this->promotionBuilder()->create();
        $data = $this->data();
        $data['id'] = $promotion->id;

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

        $promotion = $this->promotionBuilder()->create();
        $data = $this->data();
        $data['id'] = $promotion->id;

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
                promotionEdit(input:{
                    id: %d,
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
            $data['id'],
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




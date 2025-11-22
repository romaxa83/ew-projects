<?php

namespace Tests\Feature\Queries\Promotion;

use App\Models\Promotion\Promotion;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\PromotionBuilder;
use Tests\Traits\UserBuilder;

class PromotionPaginatorTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
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
            ->createRoleWithPerms([Permissions::PROMOTION_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $builder = $this->promotionBuilder();
        $builder->create();
        $builder->create();
        $builder->create();
        $builder->create();

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.promotions');

        $this->assertNotEmpty($responseData['data']);

        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('type', $responseData['data'][0]);
        $this->assertArrayHasKey('link', $responseData['data'][0]);
        $this->assertArrayHasKey('current', $responseData['data'][0]);
        $this->assertArrayHasKey('name', $responseData['data'][0]['current']);
        $this->assertArrayHasKey('department', $responseData['data'][0]);
        $this->assertArrayHasKey('id', $responseData['data'][0]['department']);

        $this->assertArrayHasKey('paginatorInfo', $responseData);
        $this->assertArrayHasKey('count', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('total', $responseData['paginatorInfo']);

        $this->assertEquals(4, $responseData['paginatorInfo']['total']);
    }

    /** @test */
    public function get_by_type()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::PROMOTION_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $builder = $this->promotionBuilder();
        $builder->setType(Promotion::TYPE_INDIVIDUAL)->create();
        $builder->setType(Promotion::TYPE_INDIVIDUAL)->create();
        $builder->setType(Promotion::TYPE_COMMON)->create();

        $response = $this->graphQL($this->getQueryStrByType(Promotion::TYPE_COMMON));

        $this->assertNotEmpty($response->json('data.promotions'));
        $this->assertEquals(1, $response->json('data.promotions.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrByType(Promotion::TYPE_INDIVIDUAL));

        $this->assertNotEmpty($response->json('data.promotions'));
        $this->assertEquals(2, $response->json('data.promotions.paginatorInfo.total'));
    }

    /** @test */
    public function get_by_active()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::PROMOTION_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $builder = $this->promotionBuilder();
        $builder->setActive(false)->create();
        $builder->create();
        $builder->create();

        $response = $this->graphQL($this->getQueryStrByActive('false'));

        $this->assertNotEmpty($response->json('data.promotions'));
        $this->assertEquals(1, $response->json('data.promotions.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrByActive('true'));

        $this->assertNotEmpty($response->json('data.promotions'));
        $this->assertEquals(2, $response->json('data.promotions.paginatorInfo.total'));
    }

    /** @test */
    public function list_empty()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::PROMOTION_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr())->assertOk();

        $responseData = $response->json('data.promotions');

        $this->assertEmpty($responseData['data']);
    }

    public function getQueryStr(): string
    {
        return  sprintf('{
            promotions {
                data{
                    id
                    type
                    link
                    current {
                        name
                    }
                    department {
                        id
                    }
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }'
        );
    }

    public function getQueryStrByType($type): string
    {
        return  sprintf('{
            promotions (type: %s) {
                data{
                    id
                    type
                    link
                    current {
                        name
                    }
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }',
        $type
        );
    }

    public function getQueryStrByActive(string $active): string
    {
        return  sprintf('{
            promotions (active: %s) {
                data{
                    id
                    type
                    link
                    current {
                        name
                    }
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }',
            $active
        );
    }
}

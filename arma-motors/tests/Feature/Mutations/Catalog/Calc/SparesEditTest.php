<?php

namespace Tests\Feature\Mutations\Catalog\Calc;

use App\Exceptions\ErrorsCode;
use App\Helpers\ConvertNumber;
use App\Models\Catalogs\Calc\Spares;
use App\Models\Catalogs\Calc\SparesGroup;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class SparesEditTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $spares = Spares::find(1);

        $this->assertNull($spares->group);

        $group = SparesGroup::where('id', 1)->first();
        $this->assertEmpty($group->spares);

        $data = [
            'id' => $spares->id,
            'groupId' => $group->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.sparesEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('article', $responseData);
        $this->assertArrayHasKey('group', $responseData);
        $this->assertArrayHasKey('id', $responseData['group']);

        $this->assertEquals($group->id, $responseData['group']['id']);

        $spares->refresh();
        $this->assertNotNull($spares->group);

        $group->refresh();
        $this->assertNotEmpty($group->spares);
    }

    /** @test */
    public function edit_only_price()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $spares = Spares::find(1);

        $data = [
            'id' => $spares->id,
            'price' => '33',
            'priceDiscount' => '288.8',
        ];
        $this->assertNotEquals($spares->price, ConvertNumber::fromFloatToNumber($data['price']));
        $this->assertNotEquals($spares->discount_price, ConvertNumber::fromFloatToNumber($data['priceDiscount']));

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyPrice($data)])
            ->assertOk();
        $responseData = $response->json('data.sparesEdit');

        $this->assertArrayHasKey('price', $responseData);
        $this->assertArrayHasKey('priceDiscount', $responseData);


        $this->assertEquals($data['price'], $responseData['price']);
        $this->assertEquals($data['priceDiscount'], $responseData['priceDiscount']);

        $spares->refresh();

        $this->assertEquals($spares->price, $responseData['price']);
        $this->assertEquals($spares->discount_price, $responseData['priceDiscount']);
    }

    /** @test */
    public function not_found_group()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => 1,
            'groupId' => 99999,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals("Validation failed for the field [sparesEdit].", $response->json('errors.0.message'));
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => 99999,
            'groupId' => 1,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();

        $data = ['id' => 1, 'groupId' => 1,];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

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

        $data = ['id' => 1, 'groupId' => 1,];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                sparesEdit(input:{
                    id: "%s",
                    groupId: %d,
                }) {
                    id
                    active
                    sort
                    type
                    article
                    name
                    group {
                        id
                    }
                }
            }',
            $data['id'],
            $data['groupId'],
        );
    }

    public static function getQueryStrOnlyPrice(array $data): string
    {
        return sprintf('
            mutation {
                sparesEdit(input:{
                    id: "%s",
                    price: "%s",
                    priceDiscount: "%s",
                }) {
                    id
                    active
                    sort
                    type
                    article
                    price
                    priceDiscount
                    name
                    group {
                        id
                    }
                }
            }',
            $data['id'],
            $data['price'],
            $data['priceDiscount'],
        );
    }
}






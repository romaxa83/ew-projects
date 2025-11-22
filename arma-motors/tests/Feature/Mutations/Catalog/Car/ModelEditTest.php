<?php

namespace Tests\Feature\Mutations\Catalog\Car;

use App\Events\ChangeHashEvent;
use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\Model;
use App\Models\Hash;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class ModelEditTest extends TestCase
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
            ->createRoleWithPerm(Permissions::CATALOG_MODEL_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Model::where('brand_id', 1)->first();

        $data = [
            'id' => $model->id,
            'sort' => 10,
            'name' => 'new_name',
            'brandId' => 2
        ];

        $this->assertTrue($model->active);
        $this->assertFalse($model->for_credit);
        $this->assertFalse($model->for_calc);
        $this->assertNotEquals($model->sort, $data['sort']);
        $this->assertNotEquals($model->name, $data['name']);
        $this->assertNotEquals($model->brand_id, $data['brandId']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $model->refresh();

        $this->assertFalse($model->active);
        $this->assertFalse($model->for_credit);
        $this->assertFalse($model->for_calc);
        $this->assertEquals($model->sort, $data['sort']);
        $this->assertEquals($model->name, $data['name']);
        $this->assertEquals($model->brand_id, $data['brandId']);

        $responseData = $response->json('data.modelEdit');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('id', $responseData['brand']);
        $this->assertEquals($responseData['name'], $data['name']);
        $this->assertEquals($responseData['id'], $data['id']);
        $this->assertEquals($responseData['brand']['id'], $data['brandId']);

        \Event::assertDispatched(function (ChangeHashEvent $event){
            return $event->alias == Hash::ALIAS_MODEL;
        });
    }

    /** @test */
    public function success_only_name()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_MODEL_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Model::where('brand_id', 1)->first();

        $data = [
            'id' => $model->id,
            'name' => 'new_name'
        ];

        $this->assertNotEquals($model->name, $data['name']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyName($data)])
            ->assertOk();

        $model->refresh();

        $this->assertEquals($model->name, $data['name']);

        $responseData = $response->json('data.modelEdit');

        $this->assertEquals($responseData['id'], $model->id);
        $this->assertEquals($responseData['name'], $data['name']);
        $this->assertEquals($responseData['sort'], $model->sort);
        $this->assertEquals($responseData['brand']['id'], $model->brand->id);
    }

    /** @test */
    public function change_credit()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_MODEL_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Model::where('brand_id', 1)->first();

        $data = [
            'id' => $model->id,
            'forCredit' => true,
            'forCalc' => false
        ];

        $this->assertFalse($model->for_credit);
        $this->assertFalse($model->for_calc);

        $response = $this->postGraphQL(['query' => $this->getQueryStrToggleCalcAndCredit($data)])
            ->assertOk();

        $model->refresh();
        $this->assertTrue($model->for_credit);
        $this->assertFalse($model->for_calc);

        $this->assertEquals($response->json('data.modelEdit.forCredit'), $data['forCredit']);
        $this->assertEquals($response->json('data.modelEdit.forCalc'), $data['forCalc']);
    }

    /** @test */
    public function change_credit_and_calc()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_MODEL_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Model::where('brand_id', 1)->first();

        $data = [
            'id' => $model->id,
            'forCredit' => true,
            'forCalc' => true
        ];

        $this->assertFalse($model->for_credit);
        $this->assertFalse($model->for_calc);

        $response = $this->postGraphQL(['query' => $this->getQueryStrToggleCalcAndCredit($data)])
            ->assertOk();

        $model->refresh();
        $this->assertTrue($model->for_credit);
        $this->assertTrue($model->for_calc);

        $this->assertEquals($response->json('data.modelEdit.forCredit'), $data['forCredit']);
        $this->assertEquals($response->json('data.modelEdit.forCalc'), $data['forCalc']);
    }

    /** @test */
    public function not_found_model()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_MODEL_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => 111111,
            'name' => 'new_name'
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyName($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_MODEL_EDIT)
            ->create();

        $data = [
            'id' => 1,
            'name' => 'new_name'
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyName($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => 1,
            'name' => 'new_name'
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyName($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }


    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                modelEdit(input: {
                    id: "%s"
                    sort: %d
                    active: false
                    brandId: "%s"
                    name: "%s"
                }) {
                    id
                    active
                    sort
                    name
                    brand {
                        id
                    }
                }
            }',
            $data['id'],
            $data['sort'],
            $data['brandId'],
            $data['name'],
        );
    }

    private function getQueryStrOnlyName(array $data): string
    {
        return sprintf('
            mutation {
                modelEdit(input: {
                    id: "%s"
                    name: "%s"
                }) {
                    id
                    active
                    name
                    sort
                    brand {
                        id
                    }
                }
            }',
            $data['id'],
            $data['name'],
        );
    }

    private function getQueryStrToggleCalcAndCredit(array $data): string
    {
        $forCredit = $data['forCredit'] ? "true" : "false";
        $forCalc = $data['forCalc'] ? "true" : "false";

        return "mutation { modelEdit(input: { id: {$data['id']} , forCredit:{$forCredit} , forCalc: {$forCalc} }) {
                    id
                    active
                    name
                    forCredit
                    forCalc
                    sort
                    brand {
                        id
                    }
                }
            }";
    }
}


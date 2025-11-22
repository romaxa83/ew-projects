<?php

namespace Tests\Feature\Mutations\Catalog\Car;

use App\Events\ChangeHashEvent;
use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Calc\Work;
use App\Models\Catalogs\Car\Brand;
use App\Models\Hash;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class BrandEditTest extends TestCase
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
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);
        $color = 3;
        $model = Brand::where('is_main', true)
            ->where('color', '!=', $color)
            ->orderBy(\DB::raw('RAND()'))->first();

        $data = [
            'id' => $model->id,
            'active' => false,
            'isMain' => false,
            'sort' => 99,
            'color' => $color,
            'name' => 'some name',
            'hourlyPayment' => 100.00,
            'discountHourlyPayment' => 90.00
        ];

        $this->assertNotEquals($model->active, $data['active']);
        $this->assertNotEquals($model->is_main, $data['isMain']);
        $this->assertNotEquals($model->sort, $data['sort']);
        $this->assertNotEquals($model->color, $data['color']);
        $this->assertNotEquals($model->name, $data['name']);
        $this->assertNotEquals($model->hourly_payment, $data['hourlyPayment']);
        $this->assertNotEquals($model->discount_hourly_payment, $data['discountHourlyPayment']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrAsString($data)])
            ->assertOk();

        $model->refresh();

        $this->assertEquals($model->active, $data['active']);
        $this->assertEquals($model->is_main, $data['isMain']);
        $this->assertEquals($model->sort, $data['sort']);
        $this->assertEquals($model->color, $data['color']);
        $this->assertEquals($model->name, $data['name']);
        $this->assertEquals($model->hourly_payment->getValue(), $data['hourlyPayment']);
        $this->assertEquals($model->discount_hourly_payment->getValue(), $data['discountHourlyPayment']);

        $responseData = $response->json('data.brandEdit');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('color', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('hourlyPayment', $responseData);
        $this->assertArrayHasKey('discountHourlyPayment', $responseData);
        $this->assertEquals($responseData['color'], $data['color']);
        $this->assertEquals($responseData['name'], $data['name']);
        $this->assertEquals($responseData['id'], $data['id']);
        $this->assertEquals($responseData['hourlyPayment'], $data['hourlyPayment']);
        $this->assertEquals($responseData['discountHourlyPayment'], $data['discountHourlyPayment']);

        \Event::assertDispatched(function (ChangeHashEvent $event){
            return $event->alias == Hash::ALIAS_BRAND;
        });
    }

    /** @test */
    public function success_edit_only_color()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $color = 3;
        $model = Brand::query()->where('color', '!=', $color)->orderBy(\DB::raw('RAND()'))->first();

        $data = [
            'id' => $model->id,
            'color' => $color
        ];

        $this->assertNotEquals($model->color, $data['color']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();
        $model->refresh();

        $this->assertEquals($model->color, $data['color']);

        $responseData = $response->json('data.brandEdit');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('color', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertEquals($responseData['color'], $data['color']);
        $this->assertEquals($responseData['id'], $data['id']);
        $this->assertEquals($responseData['active'], $model->active);
        $this->assertEquals($responseData['sort'], $model->sort);
        $this->assertEquals($responseData['isMain'], $model->is_main);
        $this->assertEquals($responseData['name'], $model->name);
    }

    /** @test */
    public function success_edit_only_hourly_payment()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Brand::query()->orderBy(\DB::raw('RAND()'))->first();

        $data = [
            'id' => $model->id,
            'hourlyPayment' => 300
        ];

        $this->assertNotEquals($model->hourly_payment->getValue(), $data['hourlyPayment']);
        $this->assertNull($model->discount_hourly_payment);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyHourlyPayment($data)])
            ->assertOk();
        $model->refresh();

        $this->assertEquals($model->hourly_payment->getValue(), $data['hourlyPayment']);

        $responseData = $response->json('data.brandEdit');

        $this->assertEquals($responseData['hourlyPayment'], $data['hourlyPayment']);
        $this->assertEquals($responseData['id'], $data['id']);
        $this->assertEquals($responseData['active'], $model->active);
        $this->assertEquals($responseData['sort'], $model->sort);
        $this->assertEquals($responseData['isMain'], $model->is_main);
        $this->assertEquals($responseData['name'], $model->name);

        $this->assertNull($model->discount_hourly_payment);
    }

    /** @test */
    public function success_edit_only_works()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Brand::query()->orderBy(\DB::raw('RAND()'))->first();

        $work1 = Work::factory()->create();
        $work2 = Work::factory()->create();
        $work3 = Work::factory()->create();

        $data = [
            'id' => $model->id,
            'work_1' => $work1->id,
            'work_2' => $work2->id,
            'work_3' => $work3->id,
        ];

        $this->assertEmpty($model->works);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyWorks($data)])
            ->assertOk();

        $model->refresh();

        $this->assertNotEmpty($model->works);
        $this->assertCount(3, $model->works);
    }

    /** @test */
    public function success_edit_only_mileage()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Brand::query()->orderBy(\DB::raw('RAND()'))->first();

        $data = [
            'id' => $model->id,
            'model_1' => 1,
            'model_2' => 2
        ];

        $hash = Hash::query()->where('alias', Hash::ALIAS_BRAND)->first();
        $this->assertEmpty($model->mileages);
        $this->assertNull($hash);

        $this->postGraphQL(['query' => $this->getQueryStrOnlyMileage($data)]);

        $model->refresh();

        $this->assertNotEmpty($model->mileages);
        $this->assertCount(2, $model->mileages);

        // проверяем что появился хеш данных
        $hash = Hash::query()->where('alias', Hash::ALIAS_BRAND)->first();
        $this->assertNotNull($hash);
        $hashValue = $hash->hash;

        $data = [
            'id' => $model->id,
            'model_1' => 4
        ];
        // делаем новый запрос , чтоб увидеть удалились не переданные данные
        $this->postGraphQL(['query' => $this->getQueryStrOnlyOneMileage($data)]);

        $model->refresh();

        $this->assertNotEmpty($model->mileages);
        $this->assertCount(1, $model->mileages);

        // хеш также должен измениться
        $hash->refresh();
        $this->assertNotEquals($hash->hash, $hashValue);
    }

    /** @test */
    public function fail_edit_wrong_color()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);
        $model = Brand::query()->orderBy(\DB::raw('RAND()'))->first();

        $data = [
            'id' => $model->id,
            'color' => 8
        ];
        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.brand.not defined color', ['color' => $data['color']]), $response->json('errors.0.message'));

    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);
        $data = [
            'id' => 9999,
            'color' => 3
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
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $data = [
            'id' => 1,
            'color' => 3
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_MODEL_EDIT)
            ->create();
        $this->loginAsAdmin($admin);
        $data = [
            'id' => 1,
            'color' => 3
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStrAsString(array $data): string
    {
        $data['active'] = $data['active'] == true ? "true": "false";
        $data['isMain'] = $data['isMain'] == true ? "true": "false";

        return sprintf('
            mutation {
                brandEdit(input: {
                    id: "%s"
                    active: %s
                    sort: %d
                    isMain: %s
                    color: %d
                    name: "%s"
                    hourlyPayment: %e
                    discountHourlyPayment: %e
                }) {
                    id
                    active
                    sort
                    color
                    isMain
                    name
                    hourlyPayment
                    discountHourlyPayment
                }
            }',
            $data['id'],
            $data['active'],
            $data['sort'],
            $data['isMain'],
            $data['color'],
            $data['name'],
            $data['hourlyPayment'],
            $data['discountHourlyPayment'],
        );

//        return "mutation { brandEdit(input:{id: {$data['id']}, active: {$data['active']}, sort: {$data['sort']}, isMain: {$data['isMain']} , color: {$data['color']}, name: \"{$data['name']}\" }) {id, active, sort, color, name}}";
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                brandEdit(input: {
                    id: "%s"
                    color: %d
                }) {
                    id
                    active
                    sort
                    color
                    isMain
                    name
                    hourlyPayment
                }
            }',
            $data['id'],
            $data['color'],
        );
    }

    private function getQueryStrOnlyHourlyPayment(array $data): string
    {
        return sprintf('
            mutation {
                brandEdit(input: {
                    id: "%s"
                    hourlyPayment: %e
                }) {
                    id
                    active
                    sort
                    color
                    isMain
                    name
                    hourlyPayment
                    discountHourlyPayment
                }
            }',
            $data['id'],
            $data['hourlyPayment'],
        );
    }

    private function getQueryStrOnlyWorks(array $data): string
    {
        return sprintf('
            mutation {
                brandEdit(input: {
                    id: "%s"
                    workIds: [%s, %s, %s]
                }) {
                    id
                    active
                    works {
                        id
                    }
                }
            }',
            $data['id'],
            $data['work_1'],
            $data['work_2'],
            $data['work_3'],
        );
    }

    private function getQueryStrOnlyMileage(array $data): string
    {
        return sprintf('
            mutation {
                brandEdit(input: {
                    id: "%s"
                    mileageIds: [%s, %s]
                }) {
                    id
                    active
                    mileages {
                        id
                    }
                }
            }',
            $data['id'],
            $data['model_1'],
            $data['model_2'],
        );
    }

    private function getQueryStrOnlyOneMileage(array $data): string
    {
        return sprintf('
            mutation {
                brandEdit(input: {
                    id: "%s"
                    mileageIds: [%s]
                }) {
                    id
                    active
                    mileages {
                        id
                    }
                }
            }',
            $data['id'],
            $data['model_1']
        );
    }
}

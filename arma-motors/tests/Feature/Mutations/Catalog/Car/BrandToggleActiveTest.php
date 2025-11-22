<?php

namespace Tests\Feature\Mutations\Catalog\Car;

use App\Events\Admin\AdminLogged;
use App\Events\ChangeHashEvent;
use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\Brand;
use App\Models\Hash;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Event;

class BrandToggleActiveTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function toggle_success()
    {
        Event::fake([ChangeHashEvent::class]);

        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Brand::orderBy(\DB::raw('RAND()'))->first();

        $this->assertTrue($model->active);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($model->id)])
            ->assertOk()
        ;

        $responseData = $response->json('data.brandToggleActive');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertFalse($responseData['active']);

        $model->refresh();
        $this->assertFalse($model->active);

        $this->postGraphQL(['query' => $this->getQueryStr($model->id)]);

        $model->refresh();
        $this->assertTrue($model->active);

        Event::assertDispatched(function (ChangeHashEvent $event){
            return $event->alias == Hash::ALIAS_BRAND;
        });
    }

    /** @test */
    public function not_found_model()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->postGraphQL(['query' => $this->getQueryStr('999')])
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr(1)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::USER_LIST)
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->postGraphQL(['query' => $this->getQueryStr(1)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(string $id): string
    {
        return sprintf('
            mutation {
                brandToggleActive(id: "%s") {
                    id
                    active
                    sort
                }
            }',
            $id,
        );
    }
}


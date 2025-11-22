<?php

namespace Tests\Feature\Mutations\Dealership;

use App\Events\ChangeHashEvent;
use App\Exceptions\ErrorsCode;
use App\Models\Dealership\Dealership;
use App\Models\Hash;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class DealershipToggleActive extends TestCase
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
            ->createRoleWithPerm(Permissions::DEALERSHIP_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Dealership::where('id',1)->first();

        $this->assertTrue($model->active);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($model->id)])
            ->assertOk();

        $responseData = $response->json('data.dealershipToggleActive');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertFalse($responseData['active']);

        $model->refresh();
        $this->assertFalse($model->active);

        $this->postGraphQL(['query' => $this->getQueryStr($model->id)]);

        $model->refresh();
        $this->assertTrue($model->active);

        \Event::assertDispatched(function (ChangeHashEvent $event){
            return $event->alias == Hash::ALIAS_DEALERSHIP;
        });

    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::DEALERSHIP_EDIT)
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
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::DEALERSHIP_EDIT)
            ->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr('999')])
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }


    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::DEALERSHIP_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->postGraphQL(['query' => $this->getQueryStr('999')])
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.errorCode'));
    }

    public static function getQueryStr(string $id): string
    {
        return sprintf('
            mutation {
                dealershipToggleActive(id: "%s") {
                    id
                    active
                    sort
                }
            }',
            $id,
        );
    }
}




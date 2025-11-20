<?php

namespace Tests\Feature\Api\Feature\Admin;

use App\Models\Report\Feature\Feature;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\FeatureService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class DeleteValueTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();

        $this->userBuilder = resolve(UserBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function success()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);;

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues('val_1', 'val_2')
            ->create();

        $this->assertCount(2, $feature->values);

        $this->deleteJson(route('admin.feature.remove-value', ['value' => $feature->values[0]]))
            ->assertJson($this->structureSuccessResponse([]))
        ;

        $feature->refresh();

        $this->assertCount(1, $feature->values);
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(FeatureService::class, function(MockInterface $mock){
            $mock->shouldReceive("removeValue")
                ->andThrows(\Exception::class, "some exception message");
        });

        /** @var $feature Feature */
        $feature = $this->featureBuilder->withTranslation()
            ->setValues('val')->create();

        $this->deleteJson(route('admin.feature.remove-value', ['value' => $feature->values[0]]))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues('val_1', 'val_2')
            ->create();

        $this->deleteJson(route('admin.feature.remove-value', ['value' => $feature->values[0]]))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues('val_1', 'val_2')
            ->create();

        $this->deleteJson(route('admin.feature.remove-value', ['value' => $feature->values[0]]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}




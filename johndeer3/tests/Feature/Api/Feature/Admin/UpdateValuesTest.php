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

class UpdateValuesTest extends TestCase
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
        $this->loginAsUser($user);

        $val_1 ='val_1';
        $val_2 ='val_2';

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues($val_1, $val_2)
            ->create();

        $data[\App::getLocale()] = 'val_1_update';

        $this->assertEquals($feature->values[0]->current->name, $val_1);

        $this->postJson(route('admin.feature.update-value', ['value' => $feature->values[0]]),
            $data,
            [
                'Content-Language' => \App::getLocale()
            ])
            ->assertJson($this->structureSuccessResponse([
                [
                    "id" => $feature->values[0]->id,
                    \App::getLocale() => $data[\App::getLocale()]
                ],
            ]))
        ;

        $feature->refresh();

        $this->assertNotEquals($feature->values[0]->current->name, $val_1);
        $this->assertEquals($feature->values[0]->current->name, $data[\App::getLocale()]);
    }

    /** @test */
    public function success_empty()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $val_1 ='val_1';
        $val_2 ='val_2';

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues($val_1, $val_2)
            ->create();

        $this->assertEquals($feature->values[0]->current->name, $val_1);

        $this->postJson(route('admin.feature.update-value', ['value' => $feature->values[0]]),
            [],
            [
                'Content-Language' => \App::getLocale()
            ])
            ->assertJson($this->structureSuccessResponse([
                [
                    "id" => $feature->values[0]->id,
                    \App::getLocale() => $feature->values[0]->current->name
                ],
            ]))
        ;

        $feature->refresh();

        $this->assertEquals($feature->values[0]->current->name, $val_1);
    }

    /** @test */
    public function success_not_create_new_local()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues('val')
            ->create();

        $data['de'] = 'val_1_update';

        $this->assertCount(1, $feature->values[0]->translates);

        $this->postJson(route('admin.feature.update-value', ['value' => $feature->values[0]]),
            $data,
            [
                'Content-Language' => \App::getLocale()
            ])
        ;

        $feature->refresh();

        $this->assertCount(1, $feature->values[0]->translates);
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(FeatureService::class, function(MockInterface $mock){
            $mock->shouldReceive("updateValue")
                ->andThrows(\Exception::class, "some exception message");
        });

        /** @var $feature Feature */
        $feature = $this->featureBuilder->withTranslation()
            ->setValues('val')->create();

        $this->postJson(route('admin.feature.update-value', ['value' => $feature->values[0]]), [])
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
            ->setValues('val')
            ->create();

        $this->postJson(route('admin.feature.update-value', ['value' => $feature->values[0]]),
            [],
            [
                'Content-Language' => \App::getLocale()
            ])
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
            ->setValues('val')
            ->create();

        $this->postJson(route('admin.feature.update-value', ['value' => $feature->values[0]]),
            [],
            [
                'Content-Language' => \App::getLocale()
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

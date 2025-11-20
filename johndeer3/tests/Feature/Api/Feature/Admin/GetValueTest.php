<?php

namespace Tests\Feature\Api\Feature\Admin;

use App\Models\Report\Feature\Feature;
use App\Models\User\Role;
use App\Models\User\User;
use App\Resources\Custom\CustomFeatureValueResource;
use App\Resources\Report\ReportFeatureListResource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class GetValueTest extends TestCase
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
        $val_3 ='val_3';

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues($val_1, $val_2)
            ->create();

        $this->featureBuilder
            ->withTranslation()
            ->setValues($val_3, $val_2)
            ->create();

        $this->getJson(route('admin.feature.get-value', ['feature' => $feature]),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureSuccessResponse([
                [
                    "id" => $feature->values[0]->id,
                    \App::getLocale() => $feature->values[0]->current->name
                ],
                [
                    "id" => $feature->values[1]->id,
                    \App::getLocale() => $feature->values[1]->current->name
                ]
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_for_select()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $val_1 ='val_1';
        $val_2 ='val_2';
        $val_3 ='val_3';

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setValues($val_1, $val_2)
            ->create();

        $this->featureBuilder
            ->withTranslation()
            ->setValues($val_3, $val_2)
            ->create();

        $this->getJson(route('admin.feature.get-value', [
            'feature' => $feature,
            'forSelect' => true,
        ]),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureSuccessResponse([
                $feature->values[0]->id => $feature->values[0]->current->name,
                $feature->values[1]->id => $feature->values[1]->current->name
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->create();

        $this->getJson(route('admin.feature.get-value', ['feature' => $feature]),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(CustomFeatureValueResource::class, function(MockInterface $mock){
            $mock->shouldReceive("fill")
                ->andThrows(\Exception::class, "some exception message");
        });

        /** @var $feature Feature */
        $feature = $this->featureBuilder->withTranslation()
            ->setValues('val')->create();

        $this->getJson(route('admin.feature.get-value', ['feature' => $feature]))
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
            ->create();

        $this->getJson(route('admin.feature.get-value', ['feature' => $feature]),[
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
            ->create();

        $this->getJson(route('admin.feature.get-value', ['feature' => $feature]),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}



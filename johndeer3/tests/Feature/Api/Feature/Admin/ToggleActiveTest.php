<?php

namespace Tests\Feature\Api\Feature\Admin;

use App\Events\DeactivateFeature;
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

class ToggleActiveTest extends TestCase
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
    public function success_toggle_to_false()
    {
        \Event::fake([DeactivateFeature::class]);

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->create();

        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->assertTrue($feature->active);

        $this->getJson(route('admin.feature.toggle-active', ['feature' => $feature]))
            ->assertJson($this->structureSuccessResponse(
                [
                    "id" => $feature->id,
                    "active" => false
                ]
            ))
        ;

        \Event::assertDispatched(DeactivateFeature::class);
        \Event::assertDispatched(DeactivateFeature::class, function ($event) use ($feature){
            return $event->feature->id === $feature->id;
        });
    }

    /** @test */
    public function success_toggle_to_true()
    {
        \Event::fake([DeactivateFeature::class]);

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setActive(false)
            ->create();

        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->assertFalse($feature->active);

        $this->getJson(route('admin.feature.toggle-active', ['feature' => $feature]))
            ->assertJson($this->structureSuccessResponse(
                [
                    "id" => $feature->id,
                    "active" => true
                ]
            ))
        ;

        \Event::assertNotDispatched(DeactivateFeature::class);
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(FeatureService::class, function(MockInterface $mock){
            $mock->shouldReceive("toggleActive")
                ->andThrows(\Exception::class, "some exception message");
        });

        $feature = $this->featureBuilder->setActive(false)->create();

        $this->getJson(route('admin.feature.toggle-active', ['feature' => $feature]))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        /** @var $feature Feature */
        $feature = $this->featureBuilder->create();

        $this->getJson(route('admin.feature.toggle-active', ['feature' => $feature]), [
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
        $feature = $this->featureBuilder->create();

        $this->getJson(route('admin.feature.toggle-active', ['feature' => $feature]), [
            'Content-Language' => \App::getLocale()
        ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}


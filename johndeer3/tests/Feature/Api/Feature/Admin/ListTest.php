<?php

namespace Tests\Feature\Api\Feature\Admin;

use App\Models\JD\EquipmentGroup;
use App\Models\Report\Feature\Feature;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\Feature\FeatureRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class ListTest extends TestCase
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
        /** @var $feature Feature */
        $this->featureBuilder->create();
        $this->featureBuilder->create();
        $this->featureBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.feature.list'))
            ->assertJsonStructure($this->structureResource(
                [
                    [
                        "id",
                        "type",
                        "position",
                        "type_field",
                        "name",
                        "unit",
                        "active",
                        "egs",
                    ]
                ]
            ))
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_filter_by_type()
    {
        $this->featureBuilder->setType(Feature::TYPE_GROUND)->create();
        $this->featureBuilder->setType(Feature::TYPE_GROUND)->create();
        $this->featureBuilder->setType(Feature::TYPE_MACHINE)->create();

        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.feature.list', ['type' => Feature::TYPE_GROUND]))
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('admin.feature.list', ['type' => Feature::TYPE_MACHINE]))
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_filter_by_eg()
    {
        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where('id', '!=', $eg_1->id)->first();

        $this->featureBuilder->setEgIds($eg_1->id)->create();
        $feature = $this->featureBuilder->setEgIds($eg_1->id, $eg_2->id)->create();
        $this->featureBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.feature.list', ['eg_id' => $eg_1->id]))
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('admin.feature.list', ['eg_id' => $eg_2->id]))
            ->assertJson(['data' => [
                ['id' => $feature->id]
            ]])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_filter_by_name()
    {
        $feature = $this->featureBuilder->withTranslation()->create();
        $this->featureBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.feature.list', ['name' => $feature->current->name]),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson(['data' => [
                ['id' => $feature->id]
            ]])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_get_not_active()
    {
        $this->featureBuilder->setActive(false)->create();

        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.feature.list'))
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.feature.list'))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(FeatureRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAll")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('admin.feature.list'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.feature.list'))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('admin.feature.list'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

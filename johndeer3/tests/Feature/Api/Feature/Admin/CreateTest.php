<?php

namespace Tests\Feature\Api\Feature\Admin;

use App\Models\JD\EquipmentGroup;
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

class CreateTest extends TestCase
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

        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where('id', '!=', $eg_1->id)->first();

        $data = self::data();
        $data['egs'] = [$eg_1->id];
        $data['sub_egs'] = [$eg_2->id];

        $this->postJson(route('admin.feature.create'), $data)
            ->assertJson($this->structureSuccessResponse(
                [
                    'type' => $data['type'],
                    'position' => $data['position'],
                    'type_field' => $data['type_field'],
                    'name' => [
                        'ru' => $data['name']['ru'],
                        'ua' => $data['name']['ua'],
                        'en' => $data['name']['en'],
                    ],
                    'unit' => [
                        'ru' => $data['unit']['ru'],
                        'ua' => $data['unit']['ua'],
                        'en' => $data['unit']['en'],
                    ],
                    'active' => null,
                    'egs' => [$eg_1->id]
                ]
            ))
        ;
    }

    /** @test */
    public function success_only_required_fields()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();
        unset($data['unit']);

        $this->postJson(route('admin.feature.create'), $data)
            ->assertJson($this->structureSuccessResponse(
                [
                    'type' => $data['type'],
                    'position' => $data['position'],
                    'type_field' => $data['type_field'],
                    'name' => [
                        'ru' => $data['name']['ru'],
                        'ua' => $data['name']['ua'],
                        'en' => $data['name']['en'],
                    ],
                    'unit' => null,
                    'active' => null,
                    'egs' => []
                ]
            ))
        ;
    }

    /** @test */
    public function fail_wrong_type()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();
        $data['type'] = 'wrong';

        $this->postJson(route('admin.feature.create'), $data)
            ->assertJson($this->structureResource(["The selected type is invalid."]))
        ;
    }

    /** @test */
    public function fail_without_type()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();
        unset($data['type']);

        $this->postJson(route('admin.feature.create'), $data)
            ->assertJson($this->structureResource(["The type field is required."]))
        ;
    }

    /** @test */
    public function fail_wrong_type_field()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();
        $data['type_field'] = 'wrong';

        $this->postJson(route('admin.feature.create'), $data)
            ->assertJson($this->structureResource(["The selected type field is invalid."]))
        ;
    }

    /** @test */
    public function fail_without_type_field()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();
        unset($data['type_field']);

        $this->postJson(route('admin.feature.create'), $data)
            ->assertJson($this->structureResource(["The type field field is required."]))
        ;
    }

    /** @test */
    public function fail_without_position()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();
        unset($data['position']);

        $this->postJson(route('admin.feature.create'), $data)
            ->assertJson($this->structureResource(["The position field is required."]))
        ;
    }

    /** @test */
    public function fail_without_name()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();
        unset($data['name']);

        $this->postJson(route('admin.feature.create'), $data)
            ->assertJson($this->structureResource(["The name field is required."]))
        ;
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(FeatureService::class, function(MockInterface $mock){
            $mock->shouldReceive("create")
                ->andThrows(\Exception::class, "some exception message");
        });

        $data = self::data();

        $this->postJson(route('admin.feature.create'), $data)
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->postJson(route('admin.feature.create'), self::data())
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->postJson(route('admin.feature.create'), self::data())
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }

    public static function data(): array
    {
        return [
            'type' => Feature::TYPE_GROUND,
            'type_field' => Feature::TYPE_FIELD_BOOL_FOR_FRONT,
            'name' => [
                'ru' => 'some feature ru',
                'ua' => 'some feature ua',
                'en' => 'some feature en',
            ],
            'unit' => [
                'ru' => 'some unit ru',
                'ua' => 'some unit ua',
                'en' => 'some unit en',
            ],
            'position' => 4
        ];
    }
}


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

class UpdateTest extends TestCase
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

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setTypeField(Feature::TYPE_FIELD_SELECT)
            ->setType(Feature::TYPE_MACHINE)
            ->setEgIds($eg_1->id)
            ->create();

        $data = CreateTest::data();
        $data['egs'] = [$eg_2->id];

        $this->assertNotEquals($feature->type, $data['type']);
        $this->assertNotEquals($feature->type_field, $data['type_field']);
        $this->assertNotEquals($feature->position, $data['position']);
        $this->assertNotNull($feature->position, $data['name'][$feature->current->lang]);
        $this->assertNotEquals($feature->current->name, $data['name'][$feature->current->lang]);
        $this->assertNotNull($feature->position, $data['unit'][$feature->current->lang]);
        $this->assertNotEquals($feature->current->unit, $data['unit'][$feature->current->lang]);
        $this->assertCount(1, $feature->egs);
        $this->assertNotEquals($feature->egs[0]->id, $data['egs'][0]);

        $this->postJson(route('admin.feature.update', ['feature' => $feature]), $data, [
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureSuccessResponse(
                [
                    'id' => $feature->id,
                    'type' => $data['type'],
                    'position' => $data['position'],
                    'type_field' => $data['type_field'],
                    'name' => [
                        'en' => $data['name']['en'],
                    ],
                    'unit' => [
                        'en' => $data['unit']['en'],
                    ],
                    'egs' => [$eg_2->id]
                ]
            ))
        ;
    }

    /** @test */
    public function success_create_translation()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->setTypeField(Feature::TYPE_FIELD_SELECT)
            ->setType(Feature::TYPE_MACHINE)
            ->create();

        $data = self::data();

        $this->assertNull($feature->current);
        $this->assertEmpty($feature->translations);

        $this->postJson(route('admin.feature.update', ['feature' => $feature]), $data, [
            'Content-Language' => \App::getLocale()
        ]);

        $feature->refresh();

        $this->assertNotNull($feature->current);
        $this->assertNotEmpty($feature->translations);
        $this->assertCount(count($data['name']), $feature->translations);
    }

    /** @test */
    public function success_only_required_fields()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $feature = $this->featureBuilder
            ->withTranslation()
            ->setTypeField(Feature::TYPE_FIELD_SELECT)
            ->setType(Feature::TYPE_MACHINE)
            ->create();

        $data = CreateTest::data();
        unset($data['unit']);

        $this->postJson(route('admin.feature.update', ['feature' => $feature]), $data, [
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureSuccessResponse(
                [
                    'id' => $feature->id,
                    'type' => $data['type'],
                    'position' => $data['position'],
                    'type_field' => $data['type_field'],
                    'name' => [
                        'en' => $data['name']['en'],
                    ],
                    'unit' => null,
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

        $feature = $this->featureBuilder->create();

        $data = CreateTest::data();
        $data['type'] = 'wrong';

        $this->postJson(route('admin.feature.update', ['feature' => $feature]), $data)
            ->assertJson($this->structureErrorResponse(["The selected type is invalid."]))
        ;
    }

    /** @test */
    public function fail_without_type()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $feature = $this->featureBuilder->create();

        $data = CreateTest::data();
        unset($data['type']);

        $this->postJson(route('admin.feature.update', ['feature' => $feature]), $data)
            ->assertJson($this->structureErrorResponse(["The type field is required."]))
        ;
    }

    /** @test */
    public function fail_wrong_type_field()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $feature = $this->featureBuilder->create();

        $data = CreateTest::data();
        $data['type_field'] = 'wrong';

        $this->postJson(route('admin.feature.update', ['feature' => $feature]), $data)
            ->assertJson($this->structureErrorResponse(["The selected type field is invalid."]))
        ;
    }

    /** @test */
    public function fail_without_type_field()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $feature = $this->featureBuilder->create();

        $data = CreateTest::data();
        unset($data['type_field']);

        $this->postJson(route('admin.feature.update', ['feature' => $feature]), $data)
            ->assertJson($this->structureErrorResponse(["The type field field is required."]))
        ;
    }

    /** @test */
    public function fail_without_position()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $feature = $this->featureBuilder->create();

        $data = CreateTest::data();
        unset($data['position']);

        $this->postJson(route('admin.feature.update', ['feature' => $feature]), $data)
            ->assertJson($this->structureErrorResponse(["The position field is required."]))
        ;
    }

    /** @test */
    public function fail_without_name()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $feature = $this->featureBuilder->create();

        $data = CreateTest::data();
        unset($data['name']);

        $this->postJson(route('admin.feature.update', ['feature' => $feature]), $data)
            ->assertJson($this->structureErrorResponse(["The name field is required."]))
        ;
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(FeatureService::class, function(MockInterface $mock){
            $mock->shouldReceive("update")
                ->andThrows(\Exception::class, "some exception message");
        });

        $feature = $this->featureBuilder->create();

        $data = CreateTest::data();

        $this->postJson(route('admin.feature.update', ['feature' => $feature]), $data)
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $feature = $this->featureBuilder->create();

        $this->postJson(route('admin.feature.update', ['feature' => $feature]), CreateTest::data())
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $feature = $this->featureBuilder->create();

        $this->postJson(route('admin.feature.update', ['feature' => $feature]), CreateTest::data())
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



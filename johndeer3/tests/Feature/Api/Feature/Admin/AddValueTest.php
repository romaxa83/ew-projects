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

class AddValueTest extends TestCase
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

        /** @var $feature Feature */
        $feature = $this->featureBuilder->create();

        $this->assertEmpty($feature->values);

        $data = [
            'ru' => 'value ru',
            'en' => 'value en',
            'ua' => 'value ua',
        ];

        $this->postJson(route('admin.feature.add-value', ['feature' => $feature]), $data)
            ->assertJsonStructure([
                "data" => [
                    [
                        "id",
                        "ru",
                        "en",
                        "ua",
                    ]
                ],
                "success"
            ])
        ;

        $feature->refresh();

        $this->assertNotEmpty($feature->values);
        $this->assertCount(1, $feature->values);
        $this->assertCount(count($data), $feature->values[0]->translates);
        foreach ($data as $lang => $name) {
            $this->assertEquals(
                $feature->values[0]->translates->where('lang', $lang)->first()->name,
                $name
            );
        }
    }

    /** @test */
    public function success_empty()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        /** @var $feature Feature */
        $feature = $this->featureBuilder->create();

        $this->assertEmpty($feature->values);

        $data = [];

        $this->postJson(route('admin.feature.add-value', ['feature' => $feature]), $data)
            ->assertJsonStructure([
                "data" => [
                    [
                        "id",
                    ]
                ],
                "success"
            ])
        ;

        $feature->refresh();

        $this->assertNotEmpty($feature->values);
        $this->assertCount(1, $feature->values);
        $this->assertEmpty($feature->values[0]->translates);
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(FeatureService::class, function(MockInterface $mock){
            $mock->shouldReceive("addValue")
                ->andThrows(\Exception::class, "some exception message");
        });

        $feature = $this->featureBuilder->create();

        $this->postJson(route('admin.feature.add-value', ['feature' => $feature]), [])
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

        $this->postJson(route('admin.feature.add-value', ['feature' => $feature]), [])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        /** @var $feature Feature */
        $feature = $this->featureBuilder->create();

        $this->postJson(route('admin.feature.add-value', ['feature' => $feature]), [])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}


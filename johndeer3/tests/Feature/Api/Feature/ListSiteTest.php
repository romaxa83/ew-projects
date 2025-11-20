<?php

namespace Tests\Feature\Api\Feature;

use App\Models\Report\Feature\Feature;
use App\Models\User\User;
use App\Repositories\Feature\FeatureRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class ListSiteTest extends TestCase
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
        $this->featureBuilder->create();
        $this->featureBuilder->create();
        $this->featureBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.report.features-field-all'))
            ->assertJsonStructure($this->structureResource(
                [
                    [
                        "id",
                        "name",
                        "unit",
                        "position",
                        "type",
                        "type_field",
                        "egs",
                        "values",
                        "group",
                        "active",
                    ]
                ]
            ))
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_one()
    {
        \App::setLocale('en');
        /** @var $feature Feature */
        $val_1 = 'val_1';
        $val_2 = 'val_2';
        $feature = $this->featureBuilder
            ->setValues($val_1, $val_2)
            ->withTranslation()
            ->create();

        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.report.features-field-all'), [
            'Content-Language' => 'en'
        ])
            ->assertJson($this->structureSuccessResponse(
                [
                    [
                        "id" => $feature->id,
                        "name" => $feature->current->name,
                        "unit" => $feature->current->unit,
                        "position" => $feature->position,
                        "type" => $feature->type,
                        "type_field" => $feature->type_field_for_front,
                        "egs" => [],
                        "values" => [
                            ['name' => $val_1],
                            ['name' => $val_2]
                        ],
                        "group" => [],
                        "active" => $feature->active,
                    ]
                ]
            ))
            ->assertJsonCount(2, 'data.0.values')
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

        $this->getJson(route('api.report.features-field-all', ['type' => Feature::TYPE_GROUND]))
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('api.report.features-field-all', ['type' => Feature::TYPE_MACHINE]))
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_not_active()
    {
        $this->featureBuilder->setActive(false)->create();

        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.report.features-field-all'))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.report.features-field-all'))
            ->assertJson(['data' => []])
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->mock(FeatureRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAllByType")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.report.features-field-all'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('api.report.features-field-all'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

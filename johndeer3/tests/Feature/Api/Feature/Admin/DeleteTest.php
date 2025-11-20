<?php

namespace Tests\Feature\Api\Feature\Admin;

use App\Models\Report\Feature\Feature;
use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $featureBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function success()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        /** @var $feature Feature */
        $feature = $this->featureBuilder->create();
        $id = $feature->id;

        $this->deleteJson(route('admin.feature.delete', ['feature' => $feature]))
            ->assertJson($this->structureSuccessResponse([]))
        ;

        $this->assertNull(Feature::find($id));
    }

    /** @test */
    public function fail_feature_attach_to_report()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->setValues('val_1')
            ->withTranslation()->create();

        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder->setRole($role)->create();

        $this->reportBuilder
            ->setUser($user)
            ->setFeatures([
                ["id" => $feature->id, "group" => [
                    ["choiceId" => $feature->values[0]->id]
                ]]
            ])
            ->setStatus(ReportStatus::CREATED)
            ->create();

        $this->deleteJson(route('admin.feature.delete', ['feature' => $feature]))
            ->assertJson($this->structureErrorResponse(__('message.can not delete features')))
        ;
    }

    /** @test */
    public function fail_feature_has_feature_type()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
            ->create();

        $this->deleteJson(route('admin.feature.delete', ['feature' => $feature]))
            ->assertJson($this->structureErrorResponse(__('message.feature has type_features')))
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

        $this->deleteJson(route('admin.feature.delete', ['feature' => $feature]))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        /** @var $feature Feature */
        $feature = $this->featureBuilder->create();

        $this->deleteJson(route('admin.feature.delete', ['feature' => $feature]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}


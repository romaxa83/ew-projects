<?php

namespace Tests\Feature\Api\Feature\Admin;

use App\Models\JD\EquipmentGroup;
use App\Models\Report\Feature\Feature;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class OneTest extends TestCase
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
        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where('id', '!=', $eg_1->id)->first();

        /** @var $feature Feature */
        $feature = $this->featureBuilder
            ->withTranslation()
            ->setTypeField(Feature::TYPE_FIELD_SELECT)
            ->setType(Feature::TYPE_MACHINE)
            ->setValues('val_1')
            ->setEgIds($eg_1->id, $eg_2->id)
            ->create();

        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.feature.show', ['feature' => $feature]), [
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureSuccessResponse(
                [
                    "id" => $feature->id,
                    "type" => $feature->type,
                    "position" => $feature->position,
                    "type_field" => $feature->type_field_for_front,
                    "name" => [
                        $feature->current->lang => $feature->current->name
                    ],
                    "unit" => [
                        $feature->current->lang => $feature->current->unit
                    ],
                    "active" => $feature->active,
                    "egs" => [
                        $eg_1->id,
                        $eg_2->id,
                    ],
                ]
            ))
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

        $this->getJson(route('admin.feature.show', ['feature' => $feature]), [
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

        $this->getJson(route('admin.feature.show', ['feature' => $feature]), [
            'Content-Language' => \App::getLocale()
        ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

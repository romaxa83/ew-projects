<?php

namespace Tests\Feature\Api\JdData\Admin;

use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class EquipmentGroupAttachTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where([['id', '!=', $eg_1->id]])->first();
        $eg_3 = EquipmentGroup::query()->where([
            ['id', '!=', $eg_1->id],
            ['id', '!=', $eg_2->id],
        ])->first();

        $this->assertEmpty($eg_1->relatedEgs);

        $this->postJson(route('admin.equipment-group.attach', [
            'EquipmentGroup' => $eg_1
        ]), ["egs" => [$eg_2->id, $eg_3->id]])
            ->assertJson($this->structureResource([
                "egs" => [
                    ["id" => $eg_2->id],
                    ["id" => $eg_3->id],
                ]
            ]))
            ->assertJsonCount(2, 'data.egs')
        ;
    }

    /** @test */
    public function success_update()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where([['id', '!=', $eg_1->id]])->first();
        $eg_3 = EquipmentGroup::query()->where([
            ['id', '!=', $eg_1->id],
            ['id', '!=', $eg_2->id],
        ])->first();

        $eg_1->relatedEgs()->attach($eg_2->id);

        $this->assertEquals($eg_1->relatedEgs->first()->id, $eg_2->id);
        $this->assertCount(1, $eg_1->relatedEgs);

        $this->postJson(route('admin.equipment-group.attach', [
            'EquipmentGroup' => $eg_1
        ]), ["egs" => [$eg_3->id]])
            ->assertJson($this->structureResource([
                "egs" => [
                    ["id" => $eg_3->id],
                ]
            ]))
            ->assertJsonCount(1, 'data.egs')
        ;
    }

    /** @test */
    public function success_delete()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where([['id', '!=', $eg_1->id]])->first();
        $eg_3 = EquipmentGroup::query()->where([
            ['id', '!=', $eg_1->id],
            ['id', '!=', $eg_2->id],
        ])->first();

        $eg_1->relatedEgs()->attach($eg_2->id);

        $this->assertEquals($eg_1->relatedEgs->first()->id, $eg_2->id);
        $this->assertCount(1, $eg_1->relatedEgs);

        $this->postJson(route('admin.equipment-group.attach', [
            'EquipmentGroup' => $eg_1
        ]), ["egs" => []])
            ->assertJson($this->structureResource([
                "egs" => null
            ]))
        ;
    }

    /** @test */
    public function success_data_empty()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();

        $this->assertEmpty($eg_1->relatedEgs);

        $this->postJson(route('admin.equipment-group.attach', [
            'EquipmentGroup' => $eg_1
        ]), [])
            ->assertJson($this->structureResource([
                "egs" => null
            ]))
        ;
    }

    /** @test */
    public function fail_wrong_eg_id()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->postJson(route('admin.equipment-group.attach', [
            'EquipmentGroup' => 9999
        ]), [])
            ->assertJson($this->structureErrorResponse("Not found model [id = 9999]"))
        ;
    }

    /** @test */
    public function fail_wrong_related_eg_id()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();

        $this->postJson(route('admin.equipment-group.attach', [
            'EquipmentGroup' => $eg_1
        ]), ["egs" => [9999]])
            ->assertJson($this->structureErrorResponse(["The selected egs.0 is invalid."]))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();

        $this->postJson(route('admin.equipment-group.attach', [
            'EquipmentGroup' => $eg_1
        ]), [])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $eg_1 = EquipmentGroup::query()->first();

        $this->postJson(route('admin.equipment-group.attach', [
            'EquipmentGroup' => $eg_1
        ]), [])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

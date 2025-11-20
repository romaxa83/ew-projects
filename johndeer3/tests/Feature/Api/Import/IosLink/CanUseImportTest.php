<?php

namespace Tests\Feature\Api\Import\IosLink;

use App\Models\Import\IosLinkImport;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class CanUseImportTest extends TestCase
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

        $this->getJson(route('admin.ios-links.import.can-use-import'))
            ->assertJson($this->structureSuccessResponse("Can"))
        ;
    }

    /** @test */
    public function success_if_status_failed_last_row()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLinkImport::factory()->create([
            "user_id" => $user->id,
            "status" => IosLinkImport::STATUS_FAILED
        ]);

        $this->getJson(route('admin.ios-links.import.can-use-import'))
            ->assertJson($this->structureSuccessResponse("Can"))
        ;
    }

    /** @test */
    public function success_if_status_done_last_row()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLinkImport::factory()->create([
            "user_id" => $user->id,
            "status" => IosLinkImport::STATUS_DONE
        ]);

        $this->getJson(route('admin.ios-links.import.can-use-import'))
            ->assertJson($this->structureSuccessResponse("Can"))
        ;
    }

    /** @test */
    public function fail_if_status_new_last_row()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLinkImport::factory()->create([
            "user_id" => $user->id,
            "status" => IosLinkImport::STATUS_NEW
        ]);

        $this->getJson(route('admin.ios-links.import.can-use-import'))
            ->assertJson($this->structureErrorResponse("Can't"))
        ;
    }

    /** @test */
    public function fail_if_status_in_process_last_row()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLinkImport::factory()->create([
            "user_id" => $user->id,
            "status" => IosLinkImport::STATUS_IN_PROCESS
        ]);

        $this->getJson(route('admin.ios-links.import.can-use-import'))
            ->assertJson($this->structureErrorResponse("Can't"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.ios-links.import.can-use-import'))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('admin.ios-links.import.can-use-import'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

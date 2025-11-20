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

class ListTest extends TestCase
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

        IosLinkImport::factory()->times(10)->create([
            "user_id" => $user->id
        ]);

        $this->getJson(route('admin.ios-links.import.index'))
            ->assertJsonStructure($this->structureWithPaginate([
                "id",
                "user_id",
                "user_name",
                "status",
                "file",
                "message",
                "created_at",
                "updated_at",
                "error_data",
            ]))
            ->assertJson([
                "meta" => [
                    'current_page' => 1,
                    'per_page' => IosLinkImport::DEFAULT_PER_PAGE,
                    'to' => 10,
                    'total' => 10
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.ios-links.import.index'))
            ->assertJson([
                "meta" => [
                    'current_page' => 1,
                    'per_page' => IosLinkImport::DEFAULT_PER_PAGE,
                    'to' => null,
                    'total' => 0
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_page()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLinkImport::factory()->times(10)->create([
            "user_id" => $user->id
        ]);

        $this->getJson(route('admin.ios-links.import.index', [
            "page" => 3
        ]))
            ->assertJson([
                "meta" => [
                    'current_page' => 3,
                    'to' => null,
                    'total' => 10
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_per_page()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLinkImport::factory()->times(10)->create([
            "user_id" => $user->id
        ]);

        $this->getJson(route('admin.ios-links.import.index', [
            "per_page" => 3
        ]))
            ->assertJson([
                "meta" => [
                    'current_page' => 1,
                    'to' => 3,
                    'per_page' => 3,
                    'total' => 10
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_id_desc()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLinkImport::factory()->times(10)->create([
            "user_id" => $user->id
        ]);

        $first = IosLinkImport::query()->orderBy('id', 'desc')->first();

        $this->getJson(route('admin.ios-links.import.index', [
            "order_by" => "id", "order_type" => "desc"
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $first->id]
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_id_asc()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLinkImport::factory()->times(1)->create([
            "user_id" => $user->id
        ]);

        $first = IosLinkImport::query()->orderBy('id', 'asc')->first();

        $this->getJson(route('admin.ios-links.import.index', [
            "order_by" => "id", "order_type" => "asc"
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $first->id]
                ]
            ])
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.ios-links.import.index'))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('admin.ios-links.import.index'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}


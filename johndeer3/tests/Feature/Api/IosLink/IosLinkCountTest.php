<?php

namespace Tests\Feature\Api\IosLink;

use App\Models\User\IosLink;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\IosLinkRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class IosLinkCountTest extends TestCase
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

        IosLink::factory()->times(13)->create();

        $this->getJson(route('admin.ios-links.count'))
            ->assertJson($this->structureSuccessResponse(13))
        ;
    }

    /** @test */
    public function success_empty()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.ios-links.count'))
            ->assertJson($this->structureSuccessResponse(0))
        ;
    }

    /** @test */
    public function success_filter_active()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->times(3)->create(['status' => true]);
        IosLink::factory()->times(5)->create(['status' => false]);

        $this->getJson(route('admin.ios-links.count', [
            "status" => 1
        ]))
            ->assertJson($this->structureSuccessResponse(3))
        ;

        $this->getJson(route('admin.ios-links.count', [
            "status" => 0
        ]))
            ->assertJson($this->structureSuccessResponse(5))
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(IosLinkRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("count")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('admin.ios-links.count'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.ios-links.count'))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('admin.ios-links.count'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

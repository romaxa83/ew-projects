<?php

namespace Tests\Feature\Api\User;

use App\Jobs\MailSendIosLinkJob;
use App\Models\User\IosLink;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use Illuminate\Support\Facades\Queue;

class SendIosLinkTest extends TestCase
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
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        Queue::fake();

        $user = $this->userBuilder->create();
        $link = IosLink::factory()->create();

        $this->assertNull($user->ios_link);

        $this->assertNull($link->user_id);
        $this->assertTrue($link->status);

        $this->putJson(route('admin.user.send-ios-link', [
            "user" => $user
        ]))
            ->assertJson($this->structureSuccessResponse(["id" => $user->id]))
        ;

        $user->refresh();
        $link->refresh();

        $this->assertEquals($user->ios_link, $link->link);

        $this->assertEquals($link->user_id, $user->id);
        $this->assertFalse($link->status);

        Queue::assertPushed(MailSendIosLinkJob::class, function ($job) use ($user) {
            return $job->getUser()->id == $user->id;
        });
    }

    /** @test */
    public function fail_not_have_ios_link()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        Queue::fake();

        $user = $this->userBuilder->create();
        IosLink::factory()->create(["status" => false]);

        $this->putJson(route('admin.user.send-ios-link', [
            "user" => $user
        ]))
            ->assertJson($this->structureErrorResponse("Not empty ios link"))
        ;

        Queue::assertNotPushed(MailSendIosLinkJob::class);
    }

    /** @test */
    public function fail_not_active_ios_link()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        Queue::fake();

        $user = $this->userBuilder->create();

        $this->putJson(route('admin.user.send-ios-link', [
            "user" => $user
        ]))
            ->assertJson($this->structureErrorResponse("Not empty ios link"))
        ;

        Queue::assertNotPushed(MailSendIosLinkJob::class);
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $user = $this->userBuilder->create();

        $this->putJson(route('admin.user.send-ios-link', [
            "user" => $user
        ]))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $user = $this->userBuilder->create();

        $this->putJson(route('admin.user.send-ios-link', [
            "user" => $user
        ]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}


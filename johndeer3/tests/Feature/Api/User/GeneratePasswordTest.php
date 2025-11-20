<?php

namespace Tests\Feature\Api\User;

use App\Jobs\MailSendJob;
use App\Models\User\IosLink;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class GeneratePasswordTest extends TestCase
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

        $link = IosLink::factory()->create();

        $user = $this->userBuilder->create();
        $oldPasswordHash = $user->password;

        \Queue::fake();

        $this->assertNull($link->user_id);
        $this->assertEquals($link->status, 1);

        $this->putJson(route('admin.user.generate-password', ['user' => $user]))
            ->assertJson($this->structureSuccessResponse(__('message.generate_password_success')))
        ;

        $user->refresh();

        $this->assertNotEquals($user->password, $oldPasswordHash);
        $this->assertEquals($user->ios_link, $link->link);

        $link->refresh();

        $this->assertEquals($link->user_id, $user->id);
        $this->assertEquals($link->status, 0);

        \Queue::assertPushed(MailSendJob::class, function ($job) {
            return $job->data['user'] instanceof User
                && $job->data['type'] == 'password'
                && $job->data['password'] == User::generateRandomPassword()
                ;
        });
    }

    /** @test */
    public function success_new_ios_link()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $link_1 = IosLink::factory()->create();
        $link_2 = IosLink::factory()->create();

        $user = $this->userBuilder->setIosLink($link_1)->create();

        \Queue::fake();

        $this->assertEquals($user->ios_link, $link_1->link);
        $this->assertEquals($user->id, $link_1->user_id);
        $this->assertEquals(1, $link_2->status);

        $this->putJson(route('admin.user.generate-password', ['user' => $user]))
            ->assertJson($this->structureSuccessResponse(__('message.generate_password_success')))
        ;

        $user->refresh();
        $link_2->refresh();

        $this->assertEquals($user->ios_link, $link_2->link);
        $this->assertEquals($user->id, $link_2->user_id);
        $this->assertEquals(0, $link_2->status);
    }

    /** @test */
    public function fail_not_have_ios_link()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $user = $this->userBuilder->create();

        \Queue::fake();

        $this->putJson(route('admin.user.generate-password', ['user' => $user]))
            ->assertJson($this->structureErrorResponse(__('message.not empty ios link')))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        IosLink::factory()->create();

        \Queue::fake();

        $this->putJson(route('admin.user.generate-password', ['user' => $user]))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $user = $this->userBuilder->create();

        IosLink::factory()->create();

        \Queue::fake();

        $this->putJson(route('admin.user.generate-password', ['user' => $user]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

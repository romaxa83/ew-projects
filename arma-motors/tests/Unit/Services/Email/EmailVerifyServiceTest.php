<?php

namespace Tests\Unit\Services\Email;

use App\Exceptions\EmailVerifyException;
use App\Models\Permission\Role;
use App\Models\User\User;
use App\Models\Verify\EmailVerify;
use App\Services\Email\EmailVerifyService;
use App\ValueObjects\Token;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmailVerifyServiceTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function success_get_token()
    {
        $user = User::factory()->create();

        $token = app(EmailVerifyService::class)->getEmailToken($user);

        $this->assertTrue($token instanceof Token);
        $this->assertNotEmpty($token->getValue());
        $this->assertNotEmpty($token->getExpires());
    }

    /** @test */
    public function success_create()
    {
        $user = User::factory()->create();

        $obj = app(EmailVerifyService::class)->create($user);

        $user->refresh();

        $this->assertTrue($obj instanceof EmailVerify);
        $this->assertNotEmpty($obj->email_token);
        $this->assertNotEmpty($obj->email_token_expires);
        $this->assertEquals($obj->entity_type, $user::class);
        $this->assertEquals($obj->entity_id, $user->id);
        $this->assertNotNull($user->emailVerifyObj);
    }

    /** @test */
    public function create_wrong_model()
    {
        $role = Role::factory()->create();

        $this->expectException(EmailVerifyException::class);

        app(EmailVerifyService::class)->create($role);
    }

    /** @test */
    public function create_if_exist_row_active_token()
    {
        $service = app(EmailVerifyService::class);
        $user = User::factory()->create();

        $service->create($user);
        $user->refresh();

        $this->expectException(EmailVerifyException::class);
        $service->create($user);
    }

    /** @test */
    public function create_if_exist_row_not_active_token()
    {
        $service = app(EmailVerifyService::class);
        $user = User::factory()->create();

        $obj = $service->create($user);
        $objId = $obj->id;
        $user->refresh();

        CarbonImmutable::setTestNow(Carbon::now()->addHour());

        $user->refresh();
        $obj = $service->create($user);

        $this->assertNotNull($user->emailVerifyObj);
        $this->assertNotEquals($objId, $obj->id);
    }
}


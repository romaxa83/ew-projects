<?php

namespace Tests\Unit\Models\Verify;

use App\Exceptions\EmailVerifyException;
use App\Models\Admin\Admin;
use App\Models\Permission\Role;
use App\Models\User\User;
use App\Models\Verify\EmailVerify;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\EmailVerifyBuilder;

class EmailVerifyTest extends TestCase
{
    use DatabaseTransactions;
    use EmailVerifyBuilder;

    /** @test */
    public function user_enabled_true()
    {
        \Config::set('user.verify_email.enabled', true);

        $this->assertTrue(EmailVerify::userEnabled());
    }

    /** @test */
    public function user_enabled_false()
    {
        \Config::set('user.verify_email.enabled', false);

        $this->assertFalse(EmailVerify::userEnabled());
    }

    /** @test */
    public function admin_enabled_true()
    {
        \Config::set('admin.verify_email.enabled', true);

        $this->assertTrue(EmailVerify::adminEnabled());
    }

    /** @test */
    public function admin_enabled_false()
    {
        \Config::set('admin.verify_email.enabled', false);

        $this->assertFalse(EmailVerify::adminEnabled());
    }

    /** @test */
    public function check_user_model()
    {
        $user = User::factory()->create();

        $this->assertTrue(EmailVerify::checkModel($user));
    }

    /** @test */
    public function check_admin_model()
    {
        $admin = Admin::factory()->create();

        $this->assertTrue(EmailVerify::checkModel($admin));
    }

    /** @test */
    public function check_wrong_model()
    {
        $model = Role::factory()->create();

        $this->expectException(EmailVerifyException::class);

        $this->assertTrue(EmailVerify::checkModel($model));
    }

    /** @test */
    public function get_token_expired()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $forUser = 'PT5M';
        $forAdmin = 'PT10M';

        \Config::set('user.verify_email.email_token_expired', $forUser);
        \Config::set('admin.verify_email.email_token_expired', $forAdmin);

        $timeUser = EmailVerify::getTokenInterval($user);
        $this->assertEquals($timeUser, $forUser);
        $this->assertNotEquals($timeUser, $forAdmin);

        $timeAdmin = EmailVerify::getTokenInterval($admin);
        $this->assertEquals($timeAdmin, $forAdmin);
        $this->assertNotEquals($timeAdmin, $forUser);
    }

    /** @test */
    public function get_token_expired_wrong()
    {
        $model = Role::factory()->create();

        $this->expectException(EmailVerifyException::class);

        EmailVerify::getTokenInterval($model);
    }

    /** @test */
    public function get_confirm_link()
    {
        $user = User::factory()->create();
        $builder = $this->emailVerifyBuilder()->setUser($user);
        $emailVerify = $builder->create();

        $link = config('app.frontend_url') . "/verify-email/" . $emailVerify->email_token->getValue();

        $this->assertEquals($emailVerify->getLinkConfirm(), $link);
    }
}



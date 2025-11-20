<?php

namespace Tests\Unit\Mail;

use App\Mail\ResetPasswordMail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\Builder\UserBuilder;
use Tests\TestCase;

class ResetPasswordMailTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        Mail::fake();

        $user = $this->userBuilder->create();

        $data = ["user" => $user];

        $model = new ResetPasswordMail($data);
        $data = $model->build();

        $this->assertEquals($data->subject, "Your password");
        $this->assertEquals($data->view, "email.reset-password");
        $this->assertEquals($data->viewData['data']['app'], "John Deere Demonstration");
        $this->assertEquals(md5($data->viewData['data']['user']), md5($user));
    }
}


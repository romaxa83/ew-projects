<?php

namespace Tests\Feature\Queries\User;

use App\Exceptions\ErrorsCode;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class UserSetNotHasNotificationTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function change_to_true_has_new_notification()
    {
        $user = User::factory()->create([
            'has_new_notifications' => true
        ]);
        $this->loginAsUser($user);

        $this->assertTrue($user->hasNewNotification());

        $response = $this->graphQL($this->getQueryStr())->assertOk();

        $this->assertTrue($response->json('data.userSetNotHasNewNotification.status'));

        $user->refresh();
        $this->assertFalse($user->hasNewNotification());
    }

    /** @test */
    public function change_to_false_has_new_notification()
    {
        $user = User::factory()->create([
            'has_new_notifications' => false
        ]);
        $this->loginAsUser($user);

        $this->assertFalse($user->hasNewNotification());

        $response = $this->graphQL($this->getQueryStr())->assertOk();

        $this->assertTrue($response->json('data.userSetNotHasNewNotification.status'));

        $user->refresh();
        $this->assertFalse($user->hasNewNotification());
    }

    /** @test */
    public function not_auth()
    {
        User::factory()->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public function getQueryStr(): string
    {
        return  sprintf('{
            userSetNotHasNewNotification {
                code
                message
                status
                }
            }'
        );
    }
}


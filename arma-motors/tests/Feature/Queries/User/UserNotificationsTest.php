<?php

namespace Tests\Feature\Queries\User;

use App\Exceptions\ErrorsCode;
use App\Models\Notification\Fcm;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class UserNotificationsTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $user = $this->userBuilder()->withNotifications()->create();
        $this->loginAsUser($user);
        $count = $user->fcmNotifications->count();

        $response = $this->graphQL($this->getQueryStr())->assertOk();

        $responseData = $response->json('data.userNotifications');

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('paginatorInfo', $responseData);
        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('status', $responseData['data'][0]);
        $this->assertArrayHasKey('type', $responseData['data'][0]);
        $this->assertArrayHasKey('sendData', $responseData['data'][0]);
        $this->assertArrayHasKey('title', $responseData['data'][0]['sendData']);
        $this->assertArrayHasKey('body', $responseData['data'][0]['sendData']);

        $this->assertCount($count, $responseData['data']);
        $this->assertEquals($count, $responseData['paginatorInfo']['total']);
    }

    /** @test */
    public function status_created()
    {
        $user = $this->userBuilder()->withNotifications()->create();
        $this->loginAsUser($user);
        $count = Fcm::query()
            ->where('user_id', $user->id)
            ->where('status', Fcm::STATUS_CREATED)
        ->count();

        $response = $this->graphQL($this->getQueryStrFilterStatus(Fcm::STATUS_CREATED))
            ->assertOk();

        $responseData = $response->json('data.userNotifications');

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('paginatorInfo', $responseData);
        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('status', $responseData['data'][0]);
        $this->assertArrayHasKey('type', $responseData['data'][0]);
        $this->assertArrayHasKey('sendData', $responseData['data'][0]);

        $this->assertCount($count, $responseData['data']);
        $this->assertEquals($count, $responseData['paginatorInfo']['total']);
    }

    /** @test */
    public function filter_by_type()
    {
        $user = $this->userBuilder()->withNotifications()->create();
        $this->loginAsUser($user);

        $countTypeNew = Fcm::query()
            ->where('user_id', $user->id)
            ->where('type', Fcm::TYPE_NEW)
            ->count();

        $response = $this->graphQL($this->getQueryStrFilterType(Fcm::TYPE_NEW))
            ->assertOk();

        $this->assertCount($countTypeNew, $response->json('data.userNotifications.data'));
        $this->assertEquals($countTypeNew, $response->json('data.userNotifications.paginatorInfo.total'));


        $countTypeCupon = Fcm::query()
            ->where('user_id', $user->id)
            ->where('type', Fcm::TYPE_CUPON)
            ->count();

        $response = $this->graphQL($this->getQueryStrFilterType(Fcm::TYPE_CUPON))
            ->assertOk();

        $this->assertCount($countTypeCupon, $response->json('data.userNotifications.data'));
        $this->assertEquals($countTypeCupon, $response->json('data.userNotifications.paginatorInfo.total'));
    }

    /** @test */
    public function empty_notification()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);
        $count = $user->fcmNotifications->count();

        $response = $this->graphQL($this->getQueryStr())->assertOk();

        $responseData = $response->json('data.userNotifications');

        $this->assertEmpty($responseData['data']);
        $this->assertEquals($count, $responseData['paginatorInfo']['total']);
    }

    /** @test */
    public function not_auth()
    {
        $this->userBuilder()->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public function getQueryStr(): string
    {
        return  sprintf('{
            userNotifications{
                data {
                    id
                    status
                    type
                    sendData
                }
                paginatorInfo {
                    total
                }
              }
            }'
        );
    }

    public function getQueryStrFilterStatus($status): string
    {
        return  sprintf('{
            userNotifications(status: %s){
                data {
                    id
                    status
                    type
                    sendData
                }
                paginatorInfo {
                    total
                }
              }
            }',
        $status
        );
    }

    public function getQueryStrFilterType($type): string
    {
        return  sprintf('{
            userNotifications(type: %s){
                data {
                    id
                    status
                    type
                    sendData
                }
                paginatorInfo {
                    total
                }
              }
            }',
            $type
        );
    }
}


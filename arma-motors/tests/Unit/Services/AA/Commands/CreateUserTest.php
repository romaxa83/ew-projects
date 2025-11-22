<?php

namespace Tests\Unit\Services\AA\Commands;

use App\Models\AA\AAResponse;
use App\Models\User\User;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Commands\CreateUser;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\User\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class CreateUserTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    /** @test */
    public function success()
    {
        // создаем пользователя
        $user = $this->userBuilder()->create();
        $user->refresh();

        // эмулируем запрос к AA
        $response = self::successData($user);
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertEmpty($user->aaResponses);

        (new CreateUser(
            $sender,
            resolve(ResponseService::class),
            resolve(UserService::class),
        ))->handler($user);

        $user->refresh();

        $this->assertNotEmpty($user->aaResponses);
        $this->assertNotEmpty($user->aaResponses[0]->status, AAResponse::STATUS_SUCCESS);
        $this->assertNotEmpty($user->aaResponses[0]->type, AAResponse::TYPE_CREATE_USER);
    }

    /** @test */
    public function something_wrong()
    {
        // создаем пользователя
        $user = $this->userBuilder()->create();
        $user->refresh();

        // эмулируем запрос к AA
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willThrowException(new AARequestException());

        $this->assertEmpty($user->aaResponses);

        (new CreateUser(
            $sender,
            resolve(ResponseService::class),
            resolve(UserService::class),
        ))->handler($user);

        $user->refresh();

        $this->assertNotEmpty($user->aaResponses);
        $this->assertEquals($user->aaResponses[0]->status, AAResponse::STATUS_ERROR);
    }

    public static function successData(User $user): array
    {
        return [
            "success" => true,
            "data" => [
                "id" => "",
                "name" => $user->name,
                "number" => $user->phone->formatAA(),
                "codeOKPO" => $user->egrpoy ?? '',
                "email" => $user->email->getValue(),
                "verified" => false
            ],
            "message" => ""
        ];
    }
}



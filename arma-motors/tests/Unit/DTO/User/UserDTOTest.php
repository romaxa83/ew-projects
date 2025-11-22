<?php

namespace Tests\Unit\DTO\User;

use App\DTO\User\UserDTO;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Tests\TestCase;

class UserDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'name' => 'test',
            'email' => 'test@test.com',
            'phone' => '30999922222',
            'password' => 'some_password',
            'egrpoy' => '123456',
            'deviceId' => 'some_device_id',
            'fcmToken' => 'some_fcm_token',
            'actionToken' => 'some_action_token',
        ];

        $dto = UserDTO::byArgs($data);

        $this->assertTrue($dto->getEmail() instanceof Email);
        $this->assertTrue($dto->getPhone() instanceof Phone);
        $this->assertEquals($dto->getEmail(), $data['email']);
        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertEquals($dto->getPhone(), $data['phone']);
        $this->assertEquals($dto->getPassword(), $data['password']);
        $this->assertEquals($dto->getEgrpoy(), $data['egrpoy']);
        $this->assertEquals($dto->getFcmToken(), $data['fcmToken']);
        $this->assertEquals($dto->getDeviceId(), $data['deviceId']);
        $this->assertEquals($dto->getActionToken(), $data['actionToken']);
        $this->assertTrue($dto->hasActionToken());
        $this->assertFalse($dto->getPhoneVerify());
    }

    /** @test */
    public function check_fill_by_args_without_required()
    {
        $data = [
            'name' => 'test',
            'phone' => '09888888888',
            'password' => 'password',
        ];

        $dto = UserDTO::byArgs($data);

        $this->assertTrue($dto->getPhone() instanceof Phone);
        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertEquals($dto->getPhone(), $data['phone']);
        $this->assertEquals($dto->getPassword(), $data['password']);
        $this->assertNull($dto->getEmail());
        $this->assertNull($dto->getEgrpoy());
        $this->assertNull($dto->getFcmToken());
        $this->assertNull($dto->getDeviceId());
        $this->assertFalse($dto->hasActionToken());
    }

    /** @test */
    public function toggle_verify_phone()
    {
        $data = [
            'name' => 'test',
            'phone' => '09888888888',
            'password' => 'password',
        ];

        $dto = UserDTO::byArgs($data);

        $this->assertFalse($dto->getPhoneVerify());

        $dto->phoneVerify();

        $this->assertTrue($dto->getPhoneVerify());
    }

    /** @test */
    public function not_valid_phone()
    {
        $data = [
            'name' => 'test',
            'phone' => 'not_valid_phone',
            'password' => 'password',
        ];

        $this->expectException(\InvalidArgumentException::class);

        UserDTO::byArgs($data);
    }
}

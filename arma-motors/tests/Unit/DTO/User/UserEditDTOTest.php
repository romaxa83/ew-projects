<?php

namespace Tests\Unit\DTO\User;

use App\DTO\User\UserDTO;
use App\DTO\User\UserEditDTO;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Tests\TestCase;

class UserEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'name' => 'test',
            'egrpoy' => '123456',
            'deviceId' => 'some_device_id',
            'fcmToken' => 'some_fcm_token',
            'lang' => 'ru'
        ];

        $dto = UserEditDTO::byArgs($data);

        $this->assertTrue(is_string($dto->getName()));
        $this->assertTrue(is_string($dto->getEgrpoy()));
        $this->assertTrue(is_string($dto->getDeviceId()));
        $this->assertTrue(is_string($dto->getFcmToken()));
        $this->assertTrue(is_string($dto->getLang()));
        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertEquals($dto->getEgrpoy(), $data['egrpoy']);
        $this->assertEquals($dto->getDeviceId(), $data['deviceId']);
        $this->assertEquals($dto->getFcmToken(), $data['fcmToken']);
        $this->assertEquals($dto->getLang(), $data['lang']);
        $this->assertTrue($dto->changeName());
        $this->assertTrue($dto->changeEgrpoy());
        $this->assertTrue($dto->changeDeviceId());
        $this->assertTrue($dto->changeFcmToken());
        $this->assertTrue($dto->changeLang());
    }

    /** @test */
    public function check_fill_empty()
    {
        $dto = UserEditDTO::byArgs([]);

        $this->assertNull($dto->getName());
        $this->assertNull($dto->getEgrpoy());
        $this->assertNull($dto->getDeviceId());
        $this->assertNull($dto->getFcmToken());
        $this->assertNull($dto->getLang());
        $this->assertFalse($dto->changeName());
        $this->assertFalse($dto->changeEgrpoy());
        $this->assertFalse($dto->changeDeviceId());
        $this->assertFalse($dto->changeFcmToken());
        $this->assertFalse($dto->changeLang());
    }

    /** @test */
    public function check_fill_null()
    {
        $data = [
            'name' => 'test',
            'egrpoy' => null,
            'deviceId' => null,
            'fcmToken' => null,
            'lang' => 'ru'
        ];

        $dto = UserEditDTO::byArgs($data);

        $this->assertNull($dto->getEgrpoy());
        $this->assertNull($dto->getDeviceId());
        $this->assertNull($dto->getFcmToken());
        $this->assertTrue($dto->changeEgrpoy());
        $this->assertTrue($dto->changeDeviceId());
        $this->assertTrue($dto->changeFcmToken());

    }

    /** @test */
    public function exception_for_name_null()
    {
        $data = [
            'name' => null,
            'lang' => 'ru',
        ];

        $this->expectException(\InvalidArgumentException::class);

        UserEditDTO::byArgs($data);
    }

    /** @test */
    public function exception_for_lang_null()
    {
        $data = [
            'name' => 'test',
            'lang' => null
        ];

        $this->expectException(\InvalidArgumentException::class);

        UserEditDTO::byArgs($data);
    }
}


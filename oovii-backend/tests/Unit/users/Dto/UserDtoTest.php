<?php

namespace Tests\Unit\users\Dto;

use Tests\TestCase;
use WezomCms\Users\Dto\UserDto;

class UserDtoTest extends TestCase
{
    /** @test */
    public function fill_by_registry_all_field()
    {
        $data = self::data();

        $dto = UserDto::byRegistry($data);

        self::assertEquals($dto->name, array_get($data, "name"));
        self::assertEquals($dto->surname, array_get($data, "surname"));
        self::assertEquals($dto->email, array_get($data, "email"));
        self::assertEquals($dto->phone, array_get($data, "phone"));
        self::assertEquals($dto->password, array_get($data, "password"));
        self::assertEquals($dto->lang, array_get($data, "lang"));
        self::assertEquals($dto->fcmToken, array_get($data, "fcmToken"));
        self::assertEquals($dto->deviceId, array_get($data, "deviceId"));
    }

    /** @test */
    public function fill_by_registry_only_required_field()
    {
        $data = self::data();

        unset(
            $data["password"],
            $data["lang"],
            $data["fcmToken"],
            $data["deviceId"],
        );

        $dto = UserDto::byRegistry($data);

        self::assertEquals($dto->name, array_get($data, "name"));
        self::assertEquals($dto->surname, array_get($data, "surname"));
        self::assertEquals($dto->email, array_get($data, "email"));
        self::assertEquals($dto->phone, array_get($data, "phone"));

        self::assertEquals($dto->password, config("cms.users.users.password_default"));
        self::assertEquals($dto->lang, config('cms.core.translations.app.default'));
        self::assertNull($dto->fcmToken);
        self::assertNull($dto->deviceId);
    }

    /** @test */
    public function fill_and_format_phone()
    {
        $phonePretty = '+380954514991';

        $phone = '+38(0954514991';
        $data = self::data();
        $data["phone"] = $phone;

        $dto = UserDto::byRegistry($data);

        self::assertEquals($dto->phone, $phonePretty);
        //================================
        $phone = '+38(095)451-49-91';
        $data = self::data();
        $data["phone"] = $phone;

        $dto = UserDto::byRegistry($data);

        self::assertEquals($dto->phone, $phonePretty);
        //================================
        $phone = '+38(095)451 49 91';
        $data = self::data();
        $data["phone"] = $phone;

        $dto = UserDto::byRegistry($data);

        self::assertEquals($dto->phone, $phonePretty);
    }

    public static function data(): array
    {
        return [
            'name' => 'test name',
            'surname' => 'test surname',
            'email' => 'vika_buraya2909@ukr.net',
            'phone' => '+380954514991',
            'password' => 'strong_password',
            'lang' => 'ru',
            'fcmToken' => 'some_fcm_token',
            'deviceId' => 'some_device_id',
        ];
    }
}

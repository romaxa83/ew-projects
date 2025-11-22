<?php

namespace Tests\Unit\Foundations\ValueObjects\Contact;

use App\Foundations\ValueObjects\Phone;
use InvalidArgumentException;
use stdClass;
use Tests\TestCase;
use TypeError;

class PhoneTest extends TestCase
{
    /** @test */
    public function create_success(): void
    {
        $phoneString = '380954500011';
        $phone = new Phone($phoneString);

        self::assertEquals($phoneString, $phone);
    }

    /** @test */
    public function create_success_without_symbols(): void
    {
        $phoneString = '+38(095)450-00-11';
        $phone = new Phone($phoneString);

        self::assertEquals('380954500011', $phone);
    }

    /** @test */
    public function has_exception_when_create_by_not_valid_email_string(): void
    {
        $notValidPhone = 'phone223';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(__('exceptions.value_object.value_must_be_phone', [
            'value' => $notValidPhone
        ]));

        new Phone($notValidPhone);
    }

    /** @test */
    public function compare_same_objects_success(): void
    {
        $phoneString = '380954500011';
        $phone_1 = new Phone($phoneString);
        $phone_2 = new Phone($phoneString);

        self::assertTrue($phone_1->compare($phone_2));
    }

    /** @test */
    public function compare_not_equals_object_fail(): void
    {
        $phone_1 = new Phone('380954500011');
        $phone_2 = new Phone('380954500012');

        self::assertFalse($phone_1->compare($phone_2));
    }

    /** @test */
    public function has_exception_when_compare_not_same_objects(): void
    {
        $phone = new Phone('380954500011');
        $object = new stdClass();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            __('exceptions.value_object.object_must_be_instance_class', ['class' => Phone::class])
        );

        $phone->compare($object);
    }
}

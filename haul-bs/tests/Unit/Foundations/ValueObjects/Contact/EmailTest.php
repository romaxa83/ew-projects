<?php

namespace Tests\Unit\Foundations\ValueObjects\Contact;

use App\Foundations\ValueObjects\Email;
use InvalidArgumentException;
use stdClass;
use Tests\TestCase;
use TypeError;

class EmailTest extends TestCase
{
    /** @test */
    public function create_success(): void
    {
        $emailString = 'valid.email@example.com';
        $email = new Email($emailString);

        self::assertEquals($emailString, $email);
    }

    /** @test */
    public function has_exception_when_create_by_not_valid_email_string(): void
    {
        $notValidEmail = 'not.valid.email@example';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(__('exceptions.value_object.value_must_be_email', [
            'value' => $notValidEmail
        ]));

        new Email($notValidEmail);
    }

    /** @test */
    public function compare_same_objects_success(): void
    {
        $emailString = 'valid.email@example.com';
        $email1 = new Email($emailString);
        $email2 = new Email($emailString);

        self::assertTrue($email1->compare($email2));
    }

    /** @test */
    public function compare_not_equals_object_fail(): void
    {
        $email1 = new Email('valid.email1@example.com');
        $email2 = new Email('valid.email2@example.com');

        self::assertFalse($email1->compare($email2));
    }

    /** @test */
    public function has_exception_when_compare_not_same_objects(): void
    {
        $email = new Email('valid.email1@example.com');
        $object = new stdClass();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            __('exceptions.value_object.object_must_be_instance_class', ['class' => Email::class])
        );

        $email->compare($object);
    }
}

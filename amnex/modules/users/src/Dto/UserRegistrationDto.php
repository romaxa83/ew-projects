<?php

declare(strict_types=1);

namespace Wezom\Users\Dto;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Wezom\Users\Rules\PasswordConfirmationRule;
use Wezom\Users\Rules\UserPasswordRule;

#[MapOutputName(SnakeCaseMapper::class)]
class UserRegistrationDto extends Data
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $password,
        public readonly string $passwordConfirmation,
    ) {
    }

    public static function rules(ValidationContext $context): array
    {
        $password = data_get($context->payload, 'password');

        return [
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'max:60', 'email:filter', 'unique:users,email'],
            'password' => ['required', 'string', new UserPasswordRule()],
            'passwordConfirmation' => [new PasswordConfirmationRule($password)],
        ];
    }

    public static function messages(): array
    {
        return [
            'email.email' => __('users::validation.site.custom.email.invalid'),
            'email.unique' => __('users::validation.site.custom.email.already_registered'),
        ];
    }

    public static function attributes(): array
    {
        return [
            'firstName' => __('users::validation.site.attributes.first_name'),
            'lastName' => __('users::validation.site.attributes.last_name'),
            'email' => __('users::validation.site.attributes.email'),
            'password' => __('users::validation.site.attributes.password'),
            'passwordConfirmation' => __('users::validation.site.attributes.password_confirmation'),
        ];
    }
}

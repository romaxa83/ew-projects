<?php

namespace App\Console\Commands\Stuff;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class BaseCreateCommand extends Command
{
    public const QUESTION_NAME = 'Name: ';
    public const QUESTION_EMAIL = 'Email: ';
    public const QUESTION_PASSWORD = 'Password: ';

    /** @throws ValidationException */
    protected function validated(): array
    {
        $validator = $this->validator(
            $args = $this->asks()
        );

        if ($validator->fails()) {
            $this->info('Staff User not created. See error messages below:');

            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            throw new ValidationException($validator);
        }

        return $args;
    }

    protected function validator(array $args): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $args,
            $this->rules()
        );
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3'],
            'email' => ['required', 'email', $this->emailUniqueRule()],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    abstract protected function emailUniqueRule(): string;

    protected function asks(): array
    {
        return [
            'name' => $this->ask(self::QUESTION_NAME),
            'email' => $this->ask(self::QUESTION_EMAIL),
            'password' => $this->ask(self::QUESTION_PASSWORD),
        ];
    }
}

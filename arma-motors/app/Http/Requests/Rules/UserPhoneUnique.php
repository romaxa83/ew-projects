<?php

namespace App\Http\Requests\Rules;

use App\Repositories\User\UserRepository;
use App\ValueObjects\Phone;
use Illuminate\Contracts\Validation\Rule;

class UserPhoneUnique implements Rule
{
    protected UserRepository $repository;
    protected $value;

    public function __construct()
    {
        $this->repository = resolve(UserRepository::class);
    }

    public function passes($attribute, $value): bool
    {
        $this->value = $value;

        return $this->repository->getByPhoneWithTrashed(new Phone($value))
            ? false
            : true;
    }

    public function message(): string|array
    {
        return __('validation.unique');
    }
}


<?php

namespace App\Http\Requests\Rules;

use App\Repositories\Dealership\DealershipRepository;
use Illuminate\Contracts\Validation\Rule;

class ExistDealership implements Rule
{
    protected DealershipRepository $repo;
    protected $value;

    public function __construct()
    {
        $this->repo = resolve(DealershipRepository::class);
    }

    public function passes($attribute, $value): bool
    {
        $this->value = $value;

        return $this->repo->existBy('alias', $value);
    }

    public function message(): string|array
    {
        return "There is no dealer center by the alias [{$this->value}]";
    }
}

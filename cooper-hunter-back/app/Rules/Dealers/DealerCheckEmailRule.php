<?php

namespace App\Rules\Dealers;

use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\ValueObjects\Email;
use Illuminate\Contracts\Validation\Rule;

class DealerCheckEmailRule implements Rule
{
    protected $email;
    protected $companyEmail;

    public function __construct(
        protected array $args
    ) {}

    public function passes($attribute, $value): bool
    {
        if($model = Company::query()->where('code', $this->args['code'])->first()){
            $this->email = $value;
            $this->companyEmail = $model->email->getValue();
            return $model->email->compare(new Email($value));
        }

        return false;
    }

    public function message(): string
    {
        return __('validation.dealer.not_compare_email', [
            'email' => $this->email,
            'contact_email' => $this->companyEmail
        ]);
    }
}

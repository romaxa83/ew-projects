<?php

namespace App\Foundations\Traits\Requests;

use App\Exceptions\ValidationException;
use App\Foundations\Services\Utils\RulesIdentifyService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;

/**
 * Trait OnlyValidateForm
 *
 * @property string errorBag
 * @property Container container
 *
 * @method array all()
 * @method array attributes()
 * @method Validator getValidatorInstance()
 * @method string getRedirectUrl()
 * @method array header($key, $default)
 * @method array messages()
 * @method array rules()
 * @method array validationData()
 *
 * @package App\Traits\Requests
 */
trait OnlyValidateForm
{
    public function rulesForOnlyReceived(): array
    {
        return resolve(RulesIdentifyService::class)->identify($this->rules(), $this->all());
    }

    /**
     * @void
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function passedValidation()
    {
        if ($this->isValidateOnly()) {
            $instance = $this->getValidatorInstance();

            $this->failedValidation($instance);
        }
    }

    protected function isValidateOnly(): bool
    {
        return !!$this->header(config('app.request_validation_only.header_key'), false);
    }

    /**
     * @param Validator $validator
     * @void
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->isValidateOnly()) {
            throw (new ValidationException($validator))
                ->errorBag($this->errorBag)
                ->redirectTo($this->getRedirectUrl());
        }

        parent::failedValidation($validator);
    }

    /**
     * @param ValidationFactory $factory
     * @return Validator
     */
    protected function createDefaultValidator(ValidationFactory $factory): Validator
    {
        if ($this->isValidateOnly()) {
            return $factory->make(
                $this->validationData(),
                $this->container->call([$this, 'rulesForOnlyReceived']),
                $this->messages(),
                $this->attributes()
            );
        }

        return parent::createDefaultValidator($factory);
    }
}


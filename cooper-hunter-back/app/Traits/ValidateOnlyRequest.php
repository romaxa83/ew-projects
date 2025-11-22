<?php

namespace App\Traits;

use App\Services\Utilities\RulesIdentifyService;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Rebing\GraphQL\Error\ValidationError;

trait ValidateOnlyRequest
{
    /**
     * @param  array  $args
     * @param  array  $rules
     * @return ValidatorContract
     * @throws ValidationError
     */
    public function getValidator(array $args, array $rules): ValidatorContract
    {
        if ($this->shouldValidateOnly($args)) {
            $validateRules = $this->resolveReceivedRules($args, $rules);

            $this->failValidation(parent::getValidator($args, $validateRules));
        }

        return parent::getValidator($args, $rules);
    }

    protected function shouldValidateOnly(array $args): bool
    {
        return $args['validate_only'] ?? false;
    }

    protected function resolveReceivedRules(array $args, array $rules): array
    {
        return resolve(RulesIdentifyService::class)->identify($rules, $args);
    }

    /**
     * @param  ValidatorContract  $validator
     * @throws ValidationError
     */
    protected function failValidation(ValidatorContract $validator): void
    {
        throw new ValidationError('validation', $validator);
    }

    protected function validateOnlyArg(): array
    {
        return [
            'validate_only' => [
                'type' => Type::boolean(),
                'defaultValue' => false,
                'description' => 'Если передан, то запрос будет только провалидирован',
            ],
        ];
    }
}

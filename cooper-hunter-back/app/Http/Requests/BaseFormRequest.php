<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\Response;

class BaseFormRequest extends FormRequest
{
    use AuthorizesRequests;

    public const PERMISSION = null;

    public function authorize(): bool
    {
        if (defined(static::class . '::PERMISSION') && static::PERMISSION) {
            return $this->user() && $this->user()->can(static::PERMISSION);
        }

        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(
                [
                    'category' => 'validation',
                    'errors' => $this->transformErrors($validator->errors()),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            )
        );
    }

    protected function transformErrors(MessageBag $errors): array
    {
        $transformed = [];

        foreach ($errors->messages() as $key => $messages) {
            $transformed[] = compact('key', 'messages');
        }

        return $transformed;
    }

    protected function getPaginationRules(): array
    {
        return [
            'per_page' => ['nullable', 'int', 'min:1', 'max:100'],
            'page' => ['nullable', 'int', 'min:1'],
        ];
    }
}

<?php

namespace Wezom\Core\Http\Controllers;

use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

abstract class ApiController
{
    protected string $fallbackLocale = 'en';

    public function success(array|int $data = [], int $status = 200): JsonResponse
    {
        if (is_int($data)) {
            return response()->json([], $data);
        }

        return response()->json($data, $status);
    }

    public function failed(Exception|array|string|int $data = [], int $status = 500): JsonResponse
    {
        if (is_int($data)) {
            return response()->json([], $data);
        }

        if (is_string($data)) {
            $data = [
                'message' => $data,
            ];
        }

        if ($data instanceof Throwable) {
            $data = [
                'message' => $data->getMessage(),
            ];
        }

        return response()->json($data, $status);
    }

    protected function prepareModelData(FormRequest $request): mixed
    {
        return $request->validated();
    }

    protected function getTranslation(
        FormRequest $request,
        string $locale,
        string $field,
        ?string $fallback = null
    ): FormRequest {
        if (is_null($fallback)) {
            $fallback = $this->fallbackLocale;
        }

        $key = str_replace('%locale%', $locale, $field);
        $fallbackKey = str_replace('%locale%', $fallback, $field);

        return $request->input($key, $request->input($fallbackKey));
    }
}

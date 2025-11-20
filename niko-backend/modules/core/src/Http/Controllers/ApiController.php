<?php

namespace WezomCms\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use WezomCms\Core\Api\ErrorCode;

class ApiController extends Controller
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * @var array
     */
    protected $locales;

    /**
     * ApiController constructor.
     */
    public function __construct()
    {
        $this->locales = array_keys(app('locales'));
    }

    /**
     * @param  Request|array  $input
     * @param  array  $attributes
     * @return array
     */
    protected function prepareLocalizedAttributes($input, array $attributes): array
    {
        $input = $input instanceof Request ? $input->all() : $input;

        $result = [];

        foreach ($this->locales as $locale) {
            $data = [];
            foreach ($attributes as $attribute) {
                $data[$attribute] = Arr::get($input, "lang.{$locale}.{$attribute}");
            }

            $result[$locale] = $data;
        }

        return $result;
    }

    protected function successJsonMessage($data, $status = 200)
    {
        return response()->json(['data' => $data], $status);
    }

    protected function successJsonCustomMessage($data, $status = 200)
    {
        return response()->json($data, $status);
    }

    protected function successEmptyMessage($status = 200)
    {
        return response()->noContent($status);
    }

    protected function errorJsonMessage($message = null, $code = ErrorCode::UNKNOWN)
    {
        return response()->json([
            'error' => [
                'message' => $message,
                'code' => $code,
            ]
        ]);
    }
}

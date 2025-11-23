<?php

namespace WezomCms\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use JsonException;
use WezomCms\Core\Traits\ApiOrderByData;

/**
 * @OA\Info(
 *     title="Oovii API documentation",
 *     version="1.0.0",
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 * @OA\Get(
 *     path="/api/resource.json",
 *     @OA\Response(response="200", description="An example resource")
 * )
 *
 * @OA\Tag(
 *     name="User",
 *     description="User and relative entity and action",
 * )
 * @OA\Server(
 *     description="stage server",
 *     url="https://oovii.wezom.agency/api/v1"
 * )
 * @OA\ExternalDocumentation(
 *     description="find more info here",
 *     url="https://swagger.io/about"
 *   )
 * @OA\SecurityScheme(
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     securityScheme="Basic"
 * )
 * @OA\Parameter(
 *     parameter="Content-Language",
 *     name="Content-Language",
 *     in="header",
 *     required=false,
 *     @OA\Schema(
 *        type="string",
 *        default="kk"
 *     )
 * )
 */

class ApiController extends Controller
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
    use ApiOrderByData;

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
        $this->checkAndFillOrderBy(request()->input('order_by') ?? $this->defaultOrderBy);
        $this->checkAndFillOrderByType(request()->input('order_type') ?? $this->defaultOrderByType);
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

    /**
     * @param JsonResponse $response
     * @param int $code
     * @param int $innerCode
     * @return JsonResponse
     * @throws JsonException
     */
    public static function successFromResponse(JsonResponse $response, $code = Response::HTTP_OK, $innerCode = 0): JsonResponse
    {
        $message = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return self::successJsonMessage($message, $code, $innerCode);
    }

    public static function successJsonMessage($message, $code = Response::HTTP_OK, $innerCode = 0): JsonResponse
    {
        $data = [
            'data' => $message,
            'success' => true,
            'code' => $innerCode
        ];

        return response()->json($data, $code);
    }

    public static function errorJsonMessage($message, $code = Response::HTTP_INTERNAL_SERVER_ERROR, $innerCode = 0): JsonResponse
    {
        if(!is_numeric($code)){
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        if($code == 0){
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return response()->json([
            'data' => $message,
            'success' => false,
            'code' => $innerCode
        ], $code);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use PHPUnit\Event\Code\Throwable;

/**
 * @see https://starkovden.github.io/step5-components-object.html
 *
 * @OA\Info(
 *     title="Haulk Body Shop API",
 *     description="API documentation",
 *     version="1.0.0",
 *     @OA\Contact(
 *         name="",
 *         email=""
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Current server"
 * )
 *
 * @OA\SecurityScheme(type="http", in="header", name="Authorization", scheme="bearer", securityScheme="Authorization")
 *
 * @OA\Parameter(parameter="Content-type", name="Content-Type", in="header", required=true,
 *      @OA\Schema(type="string", default="application/json")
 * )
 * @OA\Parameter(parameter="ValidateOnly", name="Validate-Only", in="header", required=false,
 *     description="Used by the front to validate data, you can send one or more fields; \
        if validation fails, it will return an error as a response with status 200; \
        if everything is fine, it will return an empty array, but the record will not be created or modified",
 *      @OA\Schema(type="bool", default="false")
 * )
 * @OA\Parameter(parameter="Accept", name="Accept", in="header", required=true,
 *      @OA\Schema(type="string", default="application/json")
 * )
 * @OA\Parameter(parameter="Authorization", name="Authorization", in="header", required=true,
 *     @OA\Schema(type="string", default="Bearer <authorization_token>")
 * )
 * @OA\Parameter(parameter="Authorization_EComm", name="Authorization", in="header", required=true,
 *     @OA\Schema(type="string", default="<authorization_token>")
 * )
 *
 * @OA\Parameter(parameter="Page", name="page", in="query", description="Page number", required=false,
 *     @OA\Schema(type="integer", default="1")
 * )
 * @OA\Parameter(parameter="PerPage", name="per_page", in="query", description="Number records per page", required=false,
 *     @OA\Schema(type="integer", default="10")
 * )
 * @OA\Parameter(parameter="ID", name="id", in="query", required=false, description="ID model",
 *       @OA\Schema(type="integer", example=1)
 * )
 * @OA\Parameter(parameter="IDPath", name="{id}", in="path", required=true, description="ID model",
 *      @OA\Schema(type="integer", example=1)
 * )
 * @OA\Parameter(parameter="OrderType", name="order_type", in="query", description="Type for sort", required=false,
 *      @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
 * )
 * @OA\Parameter(parameter="Limit", name="limit", in="query", description="Limit records", required=false,
 *      @OA\Schema(type="integer", default="20",)
 * )
 *
 * @OA\Parameter(parameter="Refresh-token", name="refresh_token", in="query", required=true,
 *      @OA\Schema(type="string")
 * )
 *
 * @OA\Schema(schema="Errors",
 *    @OA\Property(property="errors", description="Errors bag", type="array",
 *       @OA\Items(ref="#/components/schemas/ErrorRaw")
 *    ),
 * )
 * @OA\Schema(schema="ErrorRaw", type="object",
 *     allOf={
 *         @OA\Schema(
 *             required={"title", "status"},
 *             @OA\Property(property="title", type="string", description="Error message"),
 *             @OA\Property(property="status", type="integer", description="HTTP code or another code"),
 *         )
 *     }
 * )
 *
 * @OA\Schema(schema="ValidationErrors",
 *     @OA\Property(property="errors", description="Errors bag", type="array",
 *        @OA\Items(ref="#/components/schemas/ValidationRaw")
 *     ),
 * )
 * @OA\Schema(schema="ValidationRaw", type="object",
 *     allOf={
 *         @OA\Schema(
 *             required={"source", "title", "status"},
 *             @OA\Property(property="source", type="object",
 *                  @OA\Property(property="parameter", type="string", example="password",
 *                      description="Name of the field that did not pass validation"
 *                  ),
 *             ),
 *             @OA\Property(property="title", type="string", example="The password field is required.",
 *                  description="Error message"
 *             ),
 *             @OA\Property(property="status", type="integer", description="HTTP code", example="422"),
 *         )
 *     }
 * )
 *
 * @OA\Schema(schema="SimpleResponse",
 *     @OA\Property(property="data", type="object", allOf={
 *         @OA\Schema(
 *             required={"message"},
 *             @OA\Property(property="message", type="string", example="some messages"),
 *             )
 *         }
 *     )
 * )
 *
 *
 * @OA\Schema(schema="PaginationLinks", type="object",
 *    description="Includes useful links for pagination",
 *    allOf={
 *       @OA\Schema(
 *           required={"first", "last"},
 *           @OA\Property(property="first", type="integer", description="Link to the first page"),
 *           @OA\Property(property="last", type="integer", description="Link to the last page"),
 *           @OA\Property(property="prev", type="string", description="Link to the previous page (if exists)"),
 *           @OA\Property(property="next", type="string", description="Link to the next page (if exists)"),
 *       )
 *    }
 *  )
 * @OA\Schema(schema="PaginationMeta", type="object",
 *    description="Includes useful meta data for pagination",
 *    allOf={
 *       @OA\Schema(
 *           required={"current_page", "from", "last_page", "path", "per_page", "to", "total"},
 *           @OA\Property(property="current_page", type="integer", description="Current page number"),
 *           @OA\Property(property="from", type="integer", description="First element number on this page"),
 *           @OA\Property(property="to", type="integer", description="Last element number on this page"),
 *           @OA\Property(property="last_page", type="integer", description="Last page number"),
 *           @OA\Property(property="path", type="string", description="Canonical path to the page (first page)"),
 *           @OA\Property(property="per_page", type="integer", description="Limit of the elements on page"),
 *           @OA\Property(property="total", type="integer", description="The total count of existed elements by request"),
 *       )
 *    }
 *  )
 */

class ApiController extends Controller
{
    public function info(): JsonResponse
    {
        return response()->json([
            'version' => '1.0'
        ], Response::HTTP_OK);
    }

    public static function successJsonMessage($response = null, $statusCode = Response::HTTP_OK)
    {
        if ($response && is_string($response)) {
            $response = ['message' => $response];
        } else {
            $response = ['message' => 'Success'];
        }

        return response()->json([
            'data' => $response,
        ], $statusCode);
    }

    public function successJsonData(array $data = [], $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'data' => $data,
        ], $statusCode);
    }

    public static function errorJsonMessage($msg, $code = null)
    {
        if (is_string($msg) && $msg) {
            $msg = [$msg];
        } else {
            $msg = ['Error'];
        }
        if($code === 0){
            $code = 500;
        }

        foreach ($msg as $value) {
            $data['errors'][] = ['title' => $value, 'status' => $code];
        }

        return response()->json($data, $code);
    }
}


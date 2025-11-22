<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Resources\ApiVersionResource;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class ApiController
 * @package App\Http\Controllers
 *
 * @OA\Info(
 *      version="1.0.0",
 *      title="WEZOM: Logistics API",
 *      description="Logistics private API. Made by Wezom IT company",
 *      @OA\Contact(
 *          email="chernenko.v@wezom.com.ua"
 *      )
 * )
 * @OA\Server(
 *     url="/",
 *     description="Current server"
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     securityScheme="Authorization"
 * )
 * @OA\Parameter(
 *     parameter="Content-type",
 *     name="Content-Type",
 *     in="header",
 *     required=true,
 *     @OA\Schema(
 *        type="string",
 *        default="application/json"
 *     )
 * )
 * @OA\Parameter(
 *     parameter="ValidateOnly",
 *     name="Validate-Only",
 *     in="header",
 *     required=false,
 *     @OA\Schema(
 *        type="bool",
 *        default="false"
 *     )
 * )
 * @OA\Parameter(
 *     parameter="Accept",
 *     name="Accept",
 *     in="header",
 *     required=true,
 *     @OA\Schema(
 *        type="string",
 *        default="application/json"
 *     )
 * )
 * @OA\Parameter(
 *     parameter="Authorization",
 *     name="X-Authorization",
 *     in="header",
 *     required=true,
 *     @OA\Schema(
 *        type="string",
 *        default="Bearer <authorization_token>"
 *     )
 * )
 * @OA\Parameter(
 *     parameter="Admin-panel",
 *     name="Admin-panel",
 *     in="header",
 *     required=true,
 *     @OA\Schema(
 *        type="boolean"
 *     )
 * )
 * @OA\Parameter(
 *     parameter="Refresh-token",
 *     name="refresh_token",
 *     in="query",
 *     required=true,
 *     @OA\Schema(
 *        type="string"
 *     )
 * )
 * @OA\Schema(
 *   schema="Errors",
 *   @OA\Property(
 *      property="errors",
 *      description="Errors bag",
 *      type="array",
 *      @OA\Items(ref="#/components/schemas/Error")
 *   ),
 * )
 * @OA\Schema(
 *   schema="Error",
 *   type="object",
 *      allOf={
 *          @OA\Schema(
 *              required={"title", "status"},
 *                  @OA\Property(property="title", type="string", description="Validation message"),
 *                  @OA\Property(property="status", type="integer", description="HTTP code"),
 *                  @OA\Property(
 *                      property="source",
 *                      type="object",
 *                      description="Object with field name",
 *                      allOf={
 *                          @OA\Schema(
 *                              required={"parameter"},
 *                              @OA\Property(property="parameter", type="string", description="Field name"),
 *                          )
 *                      }
 *                  ),
 *          )
 *      }
 * )
 * @OA\Schema(
 *   schema="PaginationLinks",
 *   type="object",
 *   description="Includes useful links for pagination",
 *   allOf={
 *      @OA\Schema(
 *          required={"first", "last"},
 *          @OA\Property(property="first", type="integer", description="Link to the first page"),
 *          @OA\Property(property="last", type="integer", description="Link to the last page"),
 *          @OA\Property(property="prev", type="string", description="Link to the previous page (if exists)"),
 *          @OA\Property(property="next", type="string", description="Link to the next page (if exists)"),
 *      )
 *   }
 * )
 * @OA\Schema(
 *   schema="PaginationMeta",
 *   type="object",
 *   description="Includes useful meta data for pagination",
 *   allOf={
 *      @OA\Schema(
 *          required={"current_page", "from", "last_page", "path", "per_page", "to", "total"},
 *          @OA\Property(property="current_page", type="integer", description="Current page number"),
 *          @OA\Property(property="from", type="integer", description="First element number on this page"),
 *          @OA\Property(property="to", type="integer", description="Last element number on this page"),
 *          @OA\Property(property="last_page", type="integer", description="Last page number"),
 *          @OA\Property(property="path", type="string", description="Canonical path to the page (first page)"),
 *          @OA\Property(property="per_page", type="integer", description="Limit of the elements on page"),
 *          @OA\Property(property="total", type="integer", description="The total count of existed elements by request"),
 *      )
 *   }
 * )
 *
* <<<<<<< HEAD
 *
 * @OA\Schema(schema="SimpleResponse", type="object", title="Success with simple data",
 *      @OA\Property(property="data", type="boolean", example=true)
 *  )
 * @OA\Schema(schema="SimpleResponseAsFloatData", type="object", title="Success with simple data",
 *       @OA\Property(property="data", type="float", example=2.9)
 *   )
 *
 * @OA\Schema(schema="SuccessResponse", type="object", title="Success response",
 *       @OA\Property(property="data", type="object",
 *          @OA\Property(property="messages", type="string", example="some message"),
 *     )
 *   )
 *
 * @OA\Schema(schema="ResponseToken", type="object", title="Success response",
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="messages", type="string", example="uBCYsUGKlHN5jKreuYzHIk8SWGjn0aNURZ9J99JbIYX7ETznG3FQWWKmhob3"),
 *     )
 * )
 *
 */
class ApiController extends BaseController
{
    public int $successStatus = Response::HTTP_OK;

    public function __construct()
    {
    }

    public function api(): array
    {
        return [];
    }

    public function apiVersion(Request $request): ApiVersionResource
    {
        return ApiVersionResource::make([
            'allowed' => in_array($request->input('version'), config('routing.api_version_allowed')),
            'deprecated' => in_array($request->input('version'), config('routing.api_version_deprecated')),
            'deprecation_message' => config('routing.api_deprecation_message'),
        ]);
    }

    /**
     * Send error
     *
     * @param $response
     * @param $statusCode
     * @return JsonResponse
     */
    public function makeErrorResponse($response, $statusCode)
    {
        $data = [];
        if (is_string($response) && $response) {
            $response = [$response];
        } else {
            $response = ['Error'];
        }
        if($statusCode === 0){
            $statusCode = 500;
        }

        foreach ($response as $value) {
            $data['errors'][] = ['title' => $value, 'status' => $statusCode];
        }
        return response()->json($data, $statusCode);
    }

    /**
     * Send success answer
     *
     * @param null $response
     * @param null $statusCode
     * @return JsonResponse
     */
    public function makeSuccessResponse($response = null, $statusCode = null): JsonResponse
    {
        if (is_string($response) && $response) {
            $response = ['message' => $response];
        } else {
            $response = ['message' => 'Success'];
        }
        return response()->json(['data' => $response], $statusCode ?: $this->successStatus);
    }

    /**
     * Send success answer
     *
     * @param null $response
     * @param null $statusCode
     * @return JsonResponse
     */
    public function makeResponse($response = null, $statusCode = null): JsonResponse
    {
        return response()->json(['data' => $response], $statusCode ?: $this->successStatus);
    }

    public function authorize($ability, $arguments = [])
    {
        return parent::authorize(strtolower($ability), $arguments);
    }

    public function user(): ?User
    {
        if (!auth()->check()) {
            return null;
        }
        return auth()->user();
    }
}

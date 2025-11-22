<?php

namespace App\Http\Controllers\Api\BodyShop\Tags;

use App\Models\Tags\Tag;

class TagController extends \App\Http\Controllers\Api\Tags\TagController
{
    protected array $types = Tag::TYPES_BS;

    /**
     *
     * @OA\Get(
     *     path="/api/body-shop/tags",
     *     tags={"Tags Body Shop"},
     *     summary="Get tags list",
     *     operationId="Get Tags data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="q",
     *          in="query",
     *          description="Scope for filter by name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="name",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TagList"),
     *     )
     * )
     *
     * @OA\Post(path="/api/body-shop/tags", tags={"Tags Body Shop"}, summary="Create tag", operationId="Create tag", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="name", in="query", description="Tag name", required=true,
     *          @OA\Schema(type="string", default="Tag1",)
     *     ),
     *     @OA\Parameter(name="color", in="query", description="Tag color", required=true,
     *          @OA\Schema(type="string", default="#ffffff",)
     *     ),
     *     @OA\Parameter(name="type", in="query", description="Tag type", required=true,
     *          @OA\Schema(type="string", default="order", enum={"order, trucks_and_trailer, vehicle_owner"})
     *     ),
     *
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     * )
     *
     * @OA\Get(
     *     path="/api/body-shop/tags/{tagId}",
     *     tags={"Tags Body Shop"}, summary="Get tag data", operationId="Get tag data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     * )
     *
     * @OA\Put(
     *     path="/api/body-shop/tags/{tagId}", tags={"Tags Body Shop"}, summary="Update tag", operationId="Update tag", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Tag id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="name", in="query", description="Tag name", required=true,
     *          @OA\Schema(type="string", default="Tag1",)
     *     ),
     *     @OA\Parameter(name="color", in="query", description="Tag color", required=true,
     *          @OA\Schema(type="string", default="#ffffff",)
     *     ),
     *     @OA\Parameter(name="type", in="query", description="Tag type", required=true,
     *          @OA\Schema(type="string", default="order", enum={"order, trucks_and_trailer, vehicle_owner"})
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     * )
     *
     * @OA\Delete(
     *     path="/api/body-shop/tags/{tagId}",
     *     tags={"Tags Body Shop"}, summary="Delete tag", operationId="Delete tag", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation",),
     * )
     */

    protected function getMessageForDestroyFailed(Tag $tag): string
    {
        if ($tag->type === Tag::TYPE_TRUCKS_AND_TRAILER) {
            if ($tag->trucks()->exists() && $tag->trailers()->exists()) {
                return trans(
                    'This tag is already used for  <a href=":trucks">trucks</a> and  <a href=":trailers">trailers</a>',
                    [
                        'trucks' => str_replace('{id}', $tag->id, config('frontend.bs_trucks_with_tag_filter_url')),
                        'trailers' => str_replace('{id}', $tag->id, config('frontend.bs_trailers_with_tag_filter_url')),
                    ],
                );
            }

            if ($tag->trucks()->exists()) {
                return trans(
                    'This tag is already used for  <a href=":trucks">trucks</a>',
                    [
                        'trucks' => str_replace('{id}', $tag->id, config('frontend.bs_trucks_with_tag_filter_url')),
                    ],
                );
            }

            if ($tag->trailers()->exists()) {
                return trans(
                    'This tag is already used for  <a href=":trailers">trailers</a>',
                    [
                        'trailers' => str_replace('{id}', $tag->id, config('frontend.bs_trailers_with_tag_filter_url')),
                    ],
                );
            }
        }

        if ($tag->type === Tag::TYPE_VEHICLE_OWNER) {
            return trans(
                'This tag is already used for <a href=":link">vehicle owners</a>',
                [
                    'link' => str_replace('{id}', $tag->id, config('frontend.bs_customers_with_tag_filter_url'))
                ]
            );
        }

        return '';
    }
}

<?php

namespace WezomCms\Core\Api\Swagger;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Pagination link",
 *     description="Pagination link",
 * )
 */
class PaginationLink
{
    /**
     *  @OA\Property(property="url", title="Url", description="Ссылка", example="https://oovii.wezom.agency/api/v1/mobile/user/orders?page=1"),
     *  @OA\Property(property="lable", title="Label", description="Наименование", example="1"),
     *  @OA\Property(property="active", title="Active", description="Активность", example=false),
     */
}


<?php

namespace WezomCms\Core\Api\Swagger;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Pagination links",
 *     description="Pagination links",
 * )
 */
class PaginationLinks
{
    /**
     *  @OA\Property(property="first", title="First", description="First page link", example="https://oovii.wezom.agency/api/v1/mobile/user/orders?page=1"),
     *  @OA\Property(property="last", title="Last", description="Last page link", example="https://oovii.wezom.agency/api/v1/mobile/user/orders?page=4"),
     *  @OA\Property(property="prev", title="Prev", description="Previous page link", example="https://oovii.wezom.agency/api/v1/mobile/user/orders?page=2"),
     *  @OA\Property(property="next", title="Next", description="Next page link", example="https://oovii.wezom.agency/api/v1/mobile/user/orders?page=4"),
     */
}


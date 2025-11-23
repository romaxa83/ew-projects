<?php

namespace WezomCms\Core\Api\Swagger;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Pagination Meta",
 *     description="Pagination meta",
 * )
 */
class PaginationMeta
{
    /**
     *  @OA\Property(property="current_page", title="Current page", description="текущая страница", example=3),
     *  @OA\Property(property="from", title="From", description="Порядковый номер первого элемента", example=5),
     *  @OA\Property(property="last_page", title="Last page", description="Последняя страница", example=4),
     *  @OA\Property(property="links", title="Links", description="Ссылки на страницы пагинации", type="array",
     *      @OA\Items(ref="#/components/schemas/PaginationLink")
     *  ),
     *  @OA\Property(property="path", title="Path", description="Url запроса", example="https://oovii.wezom.agency/api/v1/mobile/user/orders"),
     *  @OA\Property(property="per_page", title="Per page", description="Количество элементов на странице", example=2),
     *  @OA\Property(property="to", title="To", description="Порядковый номер последнего элемента", example=6),
     *  @OA\Property(property="total", title="Total", description="Всего элементов", example=8),
     */
}


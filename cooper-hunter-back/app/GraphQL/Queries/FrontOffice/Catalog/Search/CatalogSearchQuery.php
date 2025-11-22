<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Search;

use App\GraphQL\Queries\FrontOffice\Catalog\Products\ProductsQuery;
use App\GraphQL\Types\Catalog\Search\CatalogSearchType;
use App\GraphQL\Types\Wrappers\Catalog\SearchResultPaginationType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL as GraphQLFacade;

class CatalogSearchQuery extends ProductsQuery
{
    public const NAME = 'catalogSearch';

    public function type(): Type
    {
        $this->prepareType();

        return CatalogSearchType::paginate();
    }

    protected function prepareType(): void
    {
        GraphQLFacade::wrapType(
            CatalogSearchType::NAME,
            CatalogSearchType::NAME . 'Pagination',
            SearchResultPaginationType::class
        );
    }
}

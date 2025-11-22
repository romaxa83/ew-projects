<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Products;

use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use App\GraphQL\Queries\Common\Catalog\Products\BaseProductQuery;
use App\Models\Catalog\Products\Product;
use Illuminate\Database\Eloquent\Builder;
use Rebing\GraphQL\Support\SelectFields;

class ProductQuery extends BaseProductQuery
{
    public function __construct()
    {
        $this->setMemberGuard();
    }

    protected function initArgs(array $args): array
    {
        if (empty($args['video_link_type'])) {
            $args['video_link_type'] = VideoLinkTypeEnum::COMMON;
        }

        return $args;
    }

    protected function getQuery(SelectFields $fields, array $args): Product|Builder
    {
        return parent::getQuery($fields, $args)
            ->where('active', true);
    }
}

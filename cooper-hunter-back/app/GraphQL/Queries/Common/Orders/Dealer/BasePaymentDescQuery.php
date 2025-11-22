<?php

namespace App\GraphQL\Queries\Common\Orders\Dealer;

use App\GraphQL\Types\Enums\Orders\Dealer\PaymentTypeTypeEnum;
use App\GraphQL\Types\Orders\Dealer\PaymentDesc\PaymentDescType;
use App\Models\About\Page;
use App\Permissions\Orders\Dealer\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BasePaymentDescQuery extends BaseQuery
{
    public const NAME = 'dealerOrdersPaymentDesc';
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            ['type' => PaymentTypeTypeEnum::type()],
        );
    }

    public function type(): Type
    {
        return PaymentDescType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        return Page::query()->paymentDesc()->filter($args)->orderBy('id')->paginate(
            perPage: $args['per_page'],
            page: $args['page']
        );
    }
}

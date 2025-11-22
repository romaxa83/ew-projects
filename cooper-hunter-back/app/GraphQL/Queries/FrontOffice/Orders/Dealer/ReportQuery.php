<?php

namespace App\GraphQL\Queries\FrontOffice\Orders\Dealer;

use App\Enums\Formats\DatetimeEnum;
use App\Enums\Orders\Dealer\OrderStatus;
use App\GraphQL\Types\Orders\Dealer\Report\ReportType;
use App\Models\Companies\Company;
use App\Models\Companies\ShippingAddress;
use App\Permissions\Orders\Dealer\ListPermission;
use App\Repositories\Orders\Dealer\OrderRepository;
use App\Services\Orders\Dealer\OrderService;
use App\Traits\GraphQL\Order\Dealer\InitArgsForFilter;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class ReportQuery extends BaseQuery
{
    use InitArgsForFilter;

    public const NAME = 'dealerOrderReport';
    public const PERMISSION = ListPermission::KEY;

    public function __construct(
        protected OrderRepository $repo,
        protected OrderService $service
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'location_id' => [
                'type' => Type::id(),
                'rules' => ['nullable', Rule::exists(ShippingAddress::TABLE, 'id')],
                'description' => 'ShippingAddressType ID'
            ],
            'company_id' => [
                'type' => Type::id(),
                'rules' => ['nullable', Rule::exists(Company::TABLE, 'id')],
                'description' => 'CompanyType ID'
            ],
            'date_from' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string', DatetimeEnum::DATE_RULE],
                'description' => 'format - Y-m-d'
            ],
            'date_to' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string', DatetimeEnum::DATE_RULE],
                'description' => 'format - Y-m-d'
            ],
        ];
    }

    public function type(): Type
    {
        return ReportType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): array
    {
        $args['status'] = OrderStatus::SHIPPED;
        $args = $this->init($args);

        $models = $this->repo->getAll([
            'dealer.company.shippingAddresses',
            'shippingAddress.company',
            'items.product'
        ], $args);

        return $this->service->transformDataForReport($models);
    }
}

<?php

namespace App\GraphQL\Queries\FrontOffice\Orders\Dealer;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\Models\Orders\Dealer\PackingSlip;
use App\Permissions\Orders\Dealer\CreatePermission;
use App\Repositories\Orders\Dealer\PackingSlipRepository;
use App\Services\Orders\Dealer\PackingSlipService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class PackingSlipPdfQuery extends BaseQuery
{
    public const NAME = 'dealerOrderPackingSlipPdf';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(
        protected PackingSlipRepository $repo,
        protected PackingSlipService $service
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
                'rules' => ['required', Rule::exists(PackingSlip::TABLE, 'id')],
                'description' => 'DealerOrderPackingSlipType ID'
            ],
        ];
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity
    {
        try {
            /** @var $packingSlip PackingSlip */
            $packingSlip = $this->repo->getBy('id', $args['id'], [
                'items',]);

            return ResponseMessageEntity::success(
                $this->service->generateAndSavePackingSlipPdfFile($packingSlip)
            );
        } catch (\Throwable $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }
}

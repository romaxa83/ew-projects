<?php

namespace App\GraphQL\Queries\BackOffice\Commercial;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\QuoteHistory;
use App\Permissions\Commercial\CommercialQuotes\CommercialQuoteListPermission;
use App\Repositories\Commercial\CommercialQuoteHistoryRepository;
use App\Services\Commercial\CommercialQuoteHistoryService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class CommercialQuoteHistoryPdfQuery extends BaseQuery
{
    public const NAME = 'commercialQuoteHistoryPdf';
    public const PERMISSION = CommercialQuoteListPermission::KEY;

    public function __construct(
        protected CommercialQuoteHistoryRepository $repo,
        protected CommercialQuoteHistoryService $service
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'History id'
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
            /** @var $history QuoteHistory */
            $history = $this->repo->getByFields(['id' => $args['id']]);

            $quote = $this->service->setQuoteFromHistory($history);

            return ResponseMessageEntity::success(
                $this->service->generateAndSavePdf($quote, $history)
            );
        } catch (\Throwable $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }
}



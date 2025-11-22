<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\CommercialQuotes;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialQuote;
use App\Models\Commercial\QuoteHistory;
use App\Notifications\Commercial\CommercialQuoteNotification;
use App\Permissions\Commercial\CommercialQuotes\CommercialQuoteUpdatePermission;
use App\Services\Commercial\CommercialQuoteHistoryService;
use App\Services\Commercial\CommercialQuoteService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialQuoteSendEmailMutation extends BaseMutation
{
    public const NAME = 'commercialQuoteSendEmail';

    public const PERMISSION = CommercialQuoteUpdatePermission::KEY;

    public function __construct(
        protected CommercialQuoteService $service,
        protected CommercialQuoteHistoryService $historyService
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(CommercialQuote::class, 'id')],
            ]
        ];
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity
    {
        try {
            /** @var $model CommercialQuote */
            $model = CommercialQuote::find($args['id']);
            /** @var $history QuoteHistory */
            $history = $this->historyService->create($model, $this->user());

            $this->historyService->generateAndSavePdf($model, $history);

            $this->service->sendEmail($model, $history);

            return ResponseMessageEntity::success(__('messages.commercial.quote.email_send'));
        } catch (\Throwable $e){
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }
}

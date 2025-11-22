<?php

namespace App\GraphQL\Types\Commercial;

use App\GraphQL\Types\Admins\AdminType;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\QuoteHistory;
use Core\Traits\Auth\AuthGuardsTrait;

class CommercialQuoteHistoryType extends BaseType
{
    use AuthGuardsTrait;

    public const NAME = 'CommercialQuoteHistoryType';
    public const MODEL = QuoteHistory::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'estimate' => [
                    'type' => NonNullType::string(),
                ],
                'position' => [
                    'type' => NonNullType::int(),
                ],
                'admin' => [
                    'type' => AdminType::type(),
                    'is_relation' => true,
                ],
                'file' => [
                    'type' => MediaType::Type(),
                    'alias' => 'media',
                    'always' => 'id',
                    'resolve' => static fn(QuoteHistory $m) => $m->getFirstMedia(QuoteHistory::MEDIA_COLLECTION_NAME)
                ],
            ],
        );
    }
}


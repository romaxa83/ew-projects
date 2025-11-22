<?php

namespace App\GraphQL\Types\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Commercial\CommercialCredentialsStatusEnumType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\UserMorphType;
use App\Models\Commercial\CredentialsRequest;
use GraphQL\Type\Definition\Type;

class CredentialsRequestType extends BaseType
{
    public const NAME = 'CredentialsRequestType';
    public const MODEL = CredentialsRequest::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'member' => [
                    'type' => UserMorphType::nonNullType(),
                ],
                'rdp_account' => [
                    'type' => RDPAccountType::type(),
                    'is_relation' => true,
                    'alias' => 'rdpAccount',
                ],
                'company_name' => [
                    'type' => NonNullType::string(),
                ],
                'company_phone' => [
                    'type' => NonNullType::string(),
                ],
                'company_email' => [
                    'type' => NonNullType::string(),
                ],
                'status' => [
                    'type' => CommercialCredentialsStatusEnumType::nonNullType(),
                ],
                'commercialProject' => [
                    'type' => CommercialProjectType::nonNullType(),
                ],
                'comment' => [
                    'type' => Type::string(),
                ],
                'processed_at' => [
                    'type' => Type::string(),
                    'resolve' => static fn(CredentialsRequest $r): ?string => $r->processed_at?->format(
                        DatetimeEnum::DATE
                    ),
                    'description' => 'Value in Y-m-d format',
                ],
                'end_date' => [
                    'type' => Type::string(),
                    'always' => 'status',
                    'resolve' => static function (CredentialsRequest $r): ?string {
                        $date = $r->status->isNew()
                            ? now()->add(config('commercial.rdp.credentials.expiration_interval'))->endOfDay()
                            : $r->end_date;

                        return $date?->format(DatetimeEnum::DATE);
                    },
                    'description' => 'Value in Y-m-d format. The date until which access will be valid, after processing the request',
                ],
            ],
        );
    }
}

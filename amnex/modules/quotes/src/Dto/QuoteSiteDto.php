<?php

declare(strict_types=1);

namespace Wezom\Quotes\Dto;

use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Wezom\Quotes\Enums\ContainerDimensionTypeEnum;
use Wezom\Quotes\Models\Terminal;

#[MapOutputName(SnakeCaseMapper::class)]
class QuoteSiteDto extends Data
{
    public function __construct(
        public readonly int $pickupTerminalId,
        public readonly string $deliveryAddress,
        public readonly bool $isOutOfGauge,
        public readonly bool $isTransload,
        public readonly ?bool $isPalletized,
        public readonly ?int $numberPallets,
        public readonly ?int $pieceCount,
        public readonly ?int $daysStored,
        public readonly ?string $containerType,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $userName,
    ) {
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'pickupTerminalId' => [
                'required',
                'integer',
                Rule::exists(Terminal::TABLE, 'id')
            ],
            'deliveryAddress' => [
                'required',
                'string',
//                Rule::exists(Terminal::TABLE, 'id'),
//                'different:pickupTerminalId'
            ],
            'isOutOfGauge' => ['required', 'boolean'],
            'containerType' => [
                Rule::requiredIf(fn () => $context->payload['isOutOfGauge'] === false),
                'string',
                ContainerDimensionTypeEnum::ruleIn()
            ],
            'isTransload' => ['required', 'boolean'],
            'isPalletized' => [
                Rule::requiredIf(fn () => $context->payload['isTransload'] === true),
                'boolean'
            ],
            'numberPallets' => [
                Rule::requiredIf(
                    fn () => isset($context->payload['isPalletized']) && $context->payload['isPalletized'] === true
                ),
                'integer',
                'min:1',
                'max:90'
            ],
            'pieceCount' => [
                Rule::requiredIf(
                    fn () => isset($context->payload['isPalletized']) && $context->payload['isPalletized'] === false
                ),
                'integer',
                'min:1',
            ],
            'daysStored' => ['nullable', 'int', 'min:1', 'max:365'],
            'email' => [
                Rule::requiredIf(fn () => $context->payload['isOutOfGauge'] === true),
                'string',
                'email',
                'max:255'
            ],
            'phone' => [
                Rule::requiredIf(fn () => $context->payload['isOutOfGauge'] === true),
                'string',
                'max:255'
            ],
            'userName' => [
                Rule::requiredIf(fn () => $context->payload['isOutOfGauge'] === true),
                'string',
                'max:255'
            ],
        ];
    }
}

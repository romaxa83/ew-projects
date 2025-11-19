<?php

declare(strict_types=1);

namespace Wezom\Admins\Dto;

use Exception;
use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Wezom\Admins\Models\Admin;
use Wezom\Core\Rules\NameRule;

#[MapOutputName(SnakeCaseMapper::class)]
class AdminDto extends Data
{
    public function __construct(
        #[Rule(new NameRule())]
        public readonly string $firstName,
        #[Rule(new NameRule())]
        public readonly string $lastName,
        #[Hidden]
        public readonly string $email,
        #[Max(15)]
        public readonly ?string $phone,
        public readonly int $roleId,
    ) {
    }

    /**
     * @throws Exception
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'email' => [
                new Required(),
                new StringType(),
                new Email(Email::FilterEmailValidation),
                new Unique(
                    table: Admin::class,
                    column: 'email',
                    ignore: $context->fullPayload['id'] ?? null,
                    ignoreColumn: 'id'
                ),
            ],
            'roleId' => [
                new Required(),
                new IntegerType(),
                new Exists(
                    table: config('permission.table_names.roles', 'roles'),
                    column: 'id',
                    where: fn ($query) => $query->where('guard_name', Admin::GUARD)
                ),
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\Source;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Wezom\Core\Annotations\GraphQlHidden;
use Wezom\Core\Annotations\GraphQlType;
use Wezom\Core\GraphQL\Types\DateForFront;
use Wezom\Core\Rules\LanguageRulesAttribute;

#[MapOutputName(SnakeCaseMapper::class)]
class EntityTestingTranslationDto extends Data
{
    public function __construct(
        #[LanguageRulesAttribute]
        public string $language,
        #[Max(254)]
        public ?string $header,
        #[Max(254)]
        public ?string $subheader,
        #[Max(254)]
        public ?string $buttonText,
        #[GraphQlType('DateForFront')]
        public Carbon $rootCarbon,
        public \Illuminate\Support\Carbon $supportCarbon,
        #[GraphQlType(DateForFront::class)]
        /** @var Collection<Carbon> */
        public Collection $dates,
        /** @var Collection<string> */
        #[GraphQlType('float')]
        public Collection $ids,
        #[GraphQlHidden]
        public string $employeeId
    ) {
    }
}

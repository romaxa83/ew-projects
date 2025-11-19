<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\Source;

use GraphQL\Type\Definition\Description;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
#[Description('Entity description')]
class EntityTestingDto extends Data
{
    public function __construct(
        #[Max(255)]
        public ?string $videoLink,
        #[Description('header size description')]
        public ?int $headerSize,
        public int $footerSize,
        public bool $backgroundOverlay,
        public ?EntityDesktopTextPositionEnum $desktopTextPosition,
        public EntityButtonTypeEnum $buttonType,
        /** @var Collection<EntityTestingTranslationDto> */
        public Collection $translations,
        /** @var Collection<int> */
        public Collection $userIds,
        /** @var Collection<int> */
        public Collection $otherIds,
        /** @var Collection<string> */
        public Collection $managerIds,
        /** @var array<string> */
        public array $ownerIds,
        public int $managerId,
        public string $clientId,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Wezom\Quotes;

use Wezom\Core\BaseServiceProvider;
use Wezom\Core\Permissions\PermissionsManager;
use Wezom\Quotes\Enums\ContainerDimensionTypeEnum;
use Wezom\Quotes\Enums\QuoteStatusEnum;
use Wezom\Quotes\Models\Quote;

class QuotesServiceProvider extends BaseServiceProvider
{
    protected array $morphMap = [
        Quote::class
    ];
    protected array $graphQlEnums = [
        QuoteStatusEnum::class,
        ContainerDimensionTypeEnum::class,
    ];
    protected array $graphQlInputs = [
        Dto\QuoteSiteDto::class,
        Dto\QuoteSiteAcceptDto::class,
        Dto\QuoteBackDto::class,
    ];

    public function permissions(PermissionsManager $permissions): void
    {

        $permissions->add(Quote::class, 'quotes::permissions.categories');
    }
}

<?php

namespace Wezom\Settings\GraphQL\Mutations\Back;

use Exception;
use Illuminate\Support\Collection;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Settings\Models\Setting;
use Wezom\Settings\Services\SettingsService;

class BackSettingsCreateOrUpdate extends BaseFieldResolver
{
    protected bool $runInTransaction = true;
    protected array $dtoRulesMap = [];

    public function __construct(
        protected SettingsService $service,
    ) {
    }

    /**
     * @throws Exception
     */
    public function resolve(Context $context): Collection
    {
        $this->service->createOrUpdate($context->getArgs()['settings'] ?? []);

        return Setting::query()
            ->get();
    }

    protected function rules(array $args = []): array
    {
        return [ ];
    }
}

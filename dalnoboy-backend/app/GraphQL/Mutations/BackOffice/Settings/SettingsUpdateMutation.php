<?php

namespace App\GraphQL\Mutations\BackOffice\Settings;

use App\Dto\Settings\SettingsDto;
use App\GraphQL\InputTypes\Settings\SettingsInputType;
use App\GraphQL\Types\Settings\SettingsType;
use App\Models\Settings\Settings;
use App\Permissions\Settings\SettingsUpdatePermission;
use App\Services\Settings\SettingsService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SettingsUpdateMutation extends BaseMutation
{
    public const NAME = 'settingsUpdate';
    public const PERMISSION = SettingsUpdatePermission::KEY;

    public function __construct(private SettingsService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return SettingsType::nonNullType();
    }

    public function args(): array
    {
        return [
            'settings' => [
                'type' => SettingsInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Settings
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Settings
    {
        return makeTransaction(
            fn() => $this->service->update(
                SettingsDto::byArgs($args['settings']),
            )
        );
    }
}

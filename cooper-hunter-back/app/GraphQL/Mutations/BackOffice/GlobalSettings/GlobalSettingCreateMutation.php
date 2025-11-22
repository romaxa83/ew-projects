<?php

namespace App\GraphQL\Mutations\BackOffice\GlobalSettings;

use App\Dto\GlobalSettings\GlobalSettingDto;
use App\GraphQL\InputTypes\GlobalSettings\GlobalSettingUpdateInput;
use App\GraphQL\Types\GlobalSettings\GlobalSettingType;
use App\Models\GlobalSettings\GlobalSetting;
use App\Permissions\GlobalSettings\GlobalSettingCreatePermission;
use App\Services\GlobalSettings\GlobalSettingService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class GlobalSettingCreateMutation extends BaseMutation
{
    public const NAME = 'globalSettingCreate';
    public const PERMISSION = GlobalSettingCreatePermission::KEY;

    public function __construct(protected GlobalSettingService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return GlobalSettingType::nonNullType();
    }

    public function args(): array
    {
        return [
            'globalSetting' => GlobalSettingUpdateInput::nonNullType(),
        ];
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
    ): GlobalSetting
    {
        if (GlobalSetting::query()->exists()) {
            throw new TranslatedException('Global settings are already exists! Please, update them.');
        }

        return makeTransaction(
            fn() => $this->service->create(
                GlobalSettingDto::buildByArgs($args['globalSetting'])
            ),
        );
    }
}

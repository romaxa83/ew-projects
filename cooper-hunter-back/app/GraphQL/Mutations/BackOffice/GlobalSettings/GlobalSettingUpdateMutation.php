<?php

namespace App\GraphQL\Mutations\BackOffice\GlobalSettings;

use App\Dto\GlobalSettings\GlobalSettingDto;
use App\Models\GlobalSettings\GlobalSetting;
use App\Permissions\GlobalSettings\GlobalSettingUpdatePermission;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class GlobalSettingUpdateMutation extends GlobalSettingCreateMutation
{
    public const NAME = 'globalSettingUpdate';
    public const PERMISSION = GlobalSettingUpdatePermission::KEY;

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): GlobalSetting {
        $globalSetting = GlobalSetting::query()->firstOrFail();

        return makeTransaction(
            fn() => $this->service->update(
                $globalSetting,
                GlobalSettingDto::buildByArgs($args['globalSetting'])
            ),
        );
    }
}

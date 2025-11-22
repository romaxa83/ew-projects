<?php

namespace App\GraphQL\Mutations\BackOffice\Utilities;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Utilities\AppVersionType;
use App\Models\Utils\Version;
use App\Permissions\Utilities\AppVersion\AppVersionPermission;
use App\Rules\Utils\VersionRule;
use App\Services\Utilities\AppVersionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CreateOrUpdateAppVersionsMutation extends BaseMutation
{
    public const NAME = 'createOrUpdateAppVersions';
    public const PERMISSION = AppVersionPermission::KEY;

    public function __construct(private AppVersionService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'recommended_version' => [
                'type' => NonNullType::string(),
            ],
            'required_version' => [
                'type' => NonNullType::string(),
            ],
        ];
    }

    public function type(): Type
    {
        return AppVersionType::nonNullType();
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
    ): Version {
        return makeTransaction(
            fn() => $this->service->createOrUpdate($args)
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'recommended_version' => [
                'required',
                'string',
                (new VersionRule())->gte($args['required_version'], 'required_version')
            ],
            'required_version' => [
                'required',
                'string',
                (new VersionRule())->lte($args['recommended_version'], 'recommended_version')
            ],
        ];
    }
}
<?php

namespace App\GraphQL\Mutations\FrontOffice\Utilities;

use App\GraphQL\Types\Enums\Utils\Versioning\VersionStatusEnumType;
use App\GraphQL\Types\NonNullType;
use App\Rules\Utils\VersionRule;
use App\Services\Utilities\AppVersionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class AppVersionStatusMutation extends BaseMutation
{
    public const NAME = 'appVersionStatus';

    public function __construct(private AppVersionService $service)
    {
    }

    public function args(): array
    {
        return [
            'version' => [
                'type' => NonNullType::string(),
                'description' => 'Version pattern: "#.#.#"',
                'rules' => ['required', 'string', new VersionRule()],
            ]
        ];
    }

    public function type(): Type
    {
        return VersionStatusEnumType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): string {
        return $this->service
            ->status($args['version'])
            ->value;
    }
}
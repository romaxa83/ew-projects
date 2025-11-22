<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\CommercialSettings;

use App\GraphQL\Types\Commercial\CommercialSettingsType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialSettings;
use App\Permissions\Commercial\CommercialSettings\CommercialSettingsUpdatePermission;
use App\Services\Commercial\CommercialSettingsService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialSettingsMutation extends BaseMutation
{
    public const NAME = 'commercialSettings';

    public const PERMISSION = CommercialSettingsUpdatePermission::KEY;

    public function __construct(private CommercialSettingsService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'nextcloud_link' => [
                'type' => NonNullType::string(),
                'rules' => ['url'],
            ],
            'quote_title' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string'],
            ],
            'quote_address_line_1' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string'],
            ],
            'quote_address_line_2' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string'],
            ],
            'quote_phone' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string'],
            ],
            'quote_email' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string'],
            ],
            'quote_site' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string'],
            ],
        ];
    }

    public function type(): Type
    {
        return CommercialSettingsType::nonNullType();
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
    ): CommercialSettings {
        return makeTransaction(
            fn() => $this->service->createOrUpdate($args)
        );
    }
}

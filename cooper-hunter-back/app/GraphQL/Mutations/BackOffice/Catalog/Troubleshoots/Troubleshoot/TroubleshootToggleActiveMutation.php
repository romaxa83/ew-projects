<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot;

use App\GraphQL\Types\Catalog\Troubleshoots\Troubleshoots;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use App\Permissions\Catalog\Troubleshoots\Troubleshoot\UpdatePermission;
use App\Services\Catalog\Troubleshoots\TroubleshootService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TroubleshootToggleActiveMutation extends BaseMutation
{
    public const NAME = 'troubleshootToggleActive';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(private TroubleshootService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Troubleshoots\TroubleshootType::type();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Troubleshoot::class, 'id')
                ]
            ],
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Model
    {
        return $this->service->toggleActive(
            Troubleshoot::find($args['id'])
        );
    }
}


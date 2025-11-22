<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Supports;

use App\Dto\Supports\SupportDto;
use App\GraphQL\InputTypes\Supports\SupportInput;
use App\GraphQL\Types\Supports\SupportType;
use App\Models\Support\Supports\Support;
use App\Permissions\Supports\SupportUpdatePermission;
use App\Services\Supports\SupportService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SupportUpdateMutation extends BaseMutation
{
    public const NAME = 'supportUpdate';
    public const PERMISSION = SupportUpdatePermission::KEY;

    public function __construct(private SupportService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return SupportType::nonNullType();
    }

    public function args(): array
    {
        return [
            'input' => SupportInput::nonNullType(),
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
    ): Support {
        return makeTransaction(
            fn() => $this->service->createOrUpdate(SupportDto::byArgs($args['input']))
        );
    }
}

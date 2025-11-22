<?php

namespace App\GraphQL\Mutations\BackOffice\About;

use App\Dto\About\ForMemberPages\ForMemberPageDto;
use App\GraphQL\InputTypes\About\ForMemberPages\ForMemberPageInput;
use App\GraphQL\Types\About\ForMemberPageType;
use App\Models\About\ForMemberPage;
use App\Permissions\About\ForMemberPages\ForMemberPageUpdatePermission;
use App\Services\About\ForMemberPageService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ForMemberPageMutation extends BaseMutation
{
    public const NAME = 'forMemberPage';
    public const PERMISSION = ForMemberPageUpdatePermission::KEY;
    public const DESCRIPTION = 'Create or Update "For Member" page';

    public function __construct(protected ForMemberPageService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ForMemberPageType::nonNullType();
    }

    public function args(): array
    {
        return [
            'input' => [
                'type' => ForMemberPageInput::nonNullType(),
            ],
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
    ): ForMemberPage {
        return makeTransaction(
            fn() => $this->service->createOrUpdate(ForMemberPageDto::byArgs($args['input']))
        );
    }
}

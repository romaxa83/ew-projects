<?php


namespace App\GraphQL\Mutations\BackOffice\Users;


use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;
use App\Permissions\Users\UserCreatePermission;
use App\Rules\ExcelRule;
use App\Services\Users\UserService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UsersImportMutation extends BaseMutation
{
    public const NAME = 'usersImport';
    public const PERMISSION = UserCreatePermission::KEY;

    public function __construct(private UserService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'file' => [
                'type' => FileType::nonNullType(),
                'description' => 'Available types: xlsx,xls,csv',
                'rules' => [
                    'required',
                    'file',
                    new ExcelRule()
                ]
            ],
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->import($args['file'])
        );
    }
}

<?php

namespace App\GraphQL\Mutations\BackOffice\About\Pages;

use App\GraphQL\Types\NonNullType;
use App\Models\About\Page;
use App\Permissions\About\Pages\PageDeletePermission;
use App\Services\About\PageService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class PageDeleteMutation extends BaseMutation
{
    public const NAME = 'pageDelete';
    public const PERMISSION = PageDeletePermission::KEY;

    public function __construct(protected PageService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Page::class, 'id')
                ]
            ],
        ];
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
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool {
        return makeTransaction(
            fn() => $this->service->delete(
                Page::find($args['id'])
            )
        );
    }
}

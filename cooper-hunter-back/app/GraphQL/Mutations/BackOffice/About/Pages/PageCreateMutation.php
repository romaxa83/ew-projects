<?php

namespace App\GraphQL\Mutations\BackOffice\About\Pages;

use App\Dto\About\Pages\PageDto;
use App\GraphQL\InputTypes\About\Pages\PageInput;
use App\GraphQL\Types\About\Pages\PageType;
use App\Models\About\Page;
use App\Permissions\About\Pages\PageCreatePermission;
use App\Services\About\PageService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class PageCreateMutation extends BaseMutation
{
    public const NAME = 'pageCreate';
    public const PERMISSION = PageCreatePermission::KEY;

    public function __construct(protected PageService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return PageType::nonNullType();
    }

    public function args(): array
    {
        return [
            'page' => [
                'type' => PageInput::nonNullType(),
            ],
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Page
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Page {
        return makeTransaction(
            fn() => $this->service->create(
                PageDto::byArgs($args['page'])
            )
        );
    }
}

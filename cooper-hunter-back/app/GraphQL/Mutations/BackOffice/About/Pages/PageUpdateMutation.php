<?php

namespace App\GraphQL\Mutations\BackOffice\About\Pages;

use App\Dto\About\Pages\PageDto;
use App\GraphQL\InputTypes\About\Pages\PageInput;
use App\GraphQL\Types\About\Pages\PageType;
use App\GraphQL\Types\NonNullType;
use App\Models\About\Page;
use App\Permissions\About\Pages\PageUpdatePermission;
use App\Services\About\PageService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class PageUpdateMutation extends BaseMutation
{
    public const NAME = 'pageUpdate';
    public const PERMISSION = PageUpdatePermission::KEY;

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
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Page::class, 'id')
                ]
            ],
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
            fn() => $this->service->update(
                PageDto::byArgs($args['page']),
                Page::find($args['id'])
            )
        );
    }
}

<?php

namespace App\GraphQL\Mutations\BackOffice\About;

use App\Dto\About\About\AboutCompanyDto;
use App\GraphQL\InputTypes\About\About\AboutCompanyInput;
use App\GraphQL\Types\About\AboutCompanyType;
use App\Models\About\AboutCompany;
use App\Permissions\About\About\AboutCompanyUpdatePermission;
use App\Services\About\AboutCompanyService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AboutCompanyMutation extends BaseMutation
{
    public const NAME = 'aboutCompany';
    public const PERMISSION = AboutCompanyUpdatePermission::KEY;

    public function __construct(protected AboutCompanyService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return AboutCompanyType::type();
    }

    public function args(): array
    {
        return [
            'about_company' => [
                'type' => AboutCompanyInput::nonNullType(),
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
    ): AboutCompany {
        return makeTransaction(
            fn() => $this->service->createOrUpdate(AboutCompanyDto::byArgs($args['about_company']))
        );
    }
}

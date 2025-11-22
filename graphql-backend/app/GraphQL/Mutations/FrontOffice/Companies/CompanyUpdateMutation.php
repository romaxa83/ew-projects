<?php

namespace App\GraphQL\Mutations\FrontOffice\Companies;

use App\Dto\Companies\CompanyDto;
use App\GraphQL\Types\Companies\CompanyType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Company;
use App\Permissions\Companies\CompanyUpdatePermission;
use App\Services\Companies\CompanyService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class CompanyUpdateMutation extends BaseMutation
{
    public const NAME = 'companyUpdate';
    public const PERMISSION = CompanyUpdatePermission::KEY;

    public function __construct(private CompanyService $companyService)
    {
    }

    public function type(): Type
    {
        return CompanyType::type();
    }

    public function args(): array
    {
        return [
            'name' => NonNullType::string(),
            'lang' => NonNullType::string(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Company
    {
        return $this->companyService->update(
            $this->company(),
            CompanyDto::byArgs($args)
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'lang' => ['required', 'string', 'exists:languages,slug']
        ];
    }
}

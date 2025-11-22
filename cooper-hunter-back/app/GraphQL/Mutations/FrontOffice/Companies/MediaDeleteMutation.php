<?php

namespace App\GraphQL\Mutations\FrontOffice\Companies;

use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\Types\Companies\CompanyType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Company;
use App\Models\Media\Media;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class MediaDeleteMutation extends BaseMutation
{
    public const NAME = 'companyMediaDelete';

    public function __construct()
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Media::class, 'id')],
                'description' => 'Media ID'
            ],
        ];
    }

    public function type(): Type
    {
        return CompanyType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Company
    {
        $media = Media::query()->where('id', $args['id'])->firstOrFail();

        $company = $media->model;

        $media->delete();

        event(new CreateOrUpdateCompanyEvent($company));

        return $company;
    }
}


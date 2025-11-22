<?php

namespace App\GraphQL\Mutations\FrontOffice\Companies;

use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\Types\Companies\CompanyType;
use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Company;
use App\Repositories\Companies\CompanyRepository;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class MediaUploadMutation extends BaseMutation
{
    public const NAME = 'companyMediaUpload';

    public function __construct(
        protected CompanyRepository $repo
    )
    {
        $this->setDealerGuard();
    }

    public function type(): Type
    {
        return CompanyType::type();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Company::class, 'id')],
                'description' => 'CompanyType ID'
            ],
            'media' => [
                'type' => FileType::nonNullType(),
                'rules' => ['file'],
            ]
        ];
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Company
    {
        /** @var $company Company */
        $company = $this->repo->getBy('id', $args['id']);

        $company->addMedia($args['media'])
            ->toMediaCollection(Company::MEDIA_COLLECTION_NAME);

        event(new CreateOrUpdateCompanyEvent($company));

        return $company;
    }
}


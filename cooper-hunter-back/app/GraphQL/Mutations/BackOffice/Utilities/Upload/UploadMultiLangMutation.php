<?php

namespace App\GraphQL\Mutations\BackOffice\Utilities\Upload;

use App\Dto\Utilities\Upload\UploadMultiLangDto;
use App\GraphQL\InputTypes\Utilities\Upload\UploadMultiLangType;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UploadMultiLangMutation extends BaseUploadMutation
{
    public const NAME = 'uploadMultiLang';

    public function args(): array
    {
        return [
            'upload' => UploadMultiLangType::nonNullType()
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
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->uploadService->upload(
                UploadMultiLangDto::byArgs($args['upload'])
            )
        );
    }
}

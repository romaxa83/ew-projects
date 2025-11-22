<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Solutions;

use App\GraphQL\InputTypes\Catalog\Solutions\FindSolutionPdfInputType;
use App\GraphQL\Types\NonNullType;
use App\Services\Catalog\Solutions\SolutionService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class FindSolutionSendPdfQuery extends BaseQuery
{
    public const NAME = 'findSolutionSendPdf';
    public const DESCRIPTION = 'Sending pdf file to email';

    public function __construct(private SolutionService $service)
    {
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    public function args(): array
    {
        return [
            'email' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'email'
                ]
            ],
            'pdf' => FindSolutionPdfInputType::nonNullType()
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): string {
        return $this->service->send($args['pdf'], $args['email']);
    }

}

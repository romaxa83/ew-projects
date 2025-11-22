<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Solutions;

use App\GraphQL\InputTypes\Catalog\Solutions\FindSolutionPdfInputType;
use App\GraphQL\Types\NonNullType;
use App\Services\Catalog\Solutions\SolutionService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class FindSolutionDownloadPdfQuery extends BaseQuery
{
    public const NAME = 'findSolutionDownloadPdf';
    public const DESCRIPTION = 'Getting URL for pdf file';

    public function __construct(private SolutionService $service)
    {
    }

    public function type(): Type
    {
        return NonNullType::string();
    }

    public function args(): array
    {
        return [
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
        logger_info("findSolutionDownloadPdf");
        return $this->service->download($args['pdf']);
    }
}

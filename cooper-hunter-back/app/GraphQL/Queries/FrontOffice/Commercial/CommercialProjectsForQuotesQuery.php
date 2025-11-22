<?php

namespace App\GraphQL\Queries\FrontOffice\Commercial;

use App\Enums\Commercial\CommercialProjectStatusEnum;
use App\Enums\Commercial\CommercialQuoteStatusEnum;
use App\GraphQL\Types\Commercial\CommercialProjectType;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectListPermission;
use Core\GraphQL\Queries\BaseQuery;
use Core\Traits\Auth\TechnicianCommercial;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class CommercialProjectsForQuotesQuery extends BaseQuery
{
    public const NAME = 'commercialProjectsForQuotes';
    public const PERMISSION = CommercialProjectListPermission::KEY;

    public function __construct()
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return CommercialProjectType::nonNullList();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->getProjects();
    }

    public function getProjects(): Collection
    {
        $this->isTechnicianCommercial();

        return $this->user()
            ->commercialProjects()
            ->where('status', CommercialProjectStatusEnum::PENDING)
            ->where('estimate_end_date', '>', now())
            ->whereDoesntHave('quotes', function($q){
                return $q->where('status', CommercialQuoteStatusEnum::PENDING());
            })
            ->get();
    }
}

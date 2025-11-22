<?php

namespace App\GraphQL\Queries\FrontOffice\Commercial;

use App\Enums\Commercial\CommercialProjectStatusEnum;
use App\GraphQL\Types\Commercial\CommercialProjectType;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class CommercialProjectsForCredentialsQuery extends BaseQuery
{
    public const NAME = 'commercialProjectsForCredentials';
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
        return $this->user()
            ->commercialProjects()
            ->where('status', CommercialProjectStatusEnum::PENDING)
            ->whereNotNull('code')
            ->where('estimate_end_date', '>', now())
            ->get();
    }
}
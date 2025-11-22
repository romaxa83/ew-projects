<?php


namespace App\GraphQL\Queries\Common\SupportRequests;


use App\GraphQL\Types\SupportRequests\Subjects\SupportRequestSubjectType;
use App\Permissions\SupportRequests\Subjects\SupportRequestSubjectListPermission;
use App\Services\SupportRequests\SupportRequestSubjectService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseSupportRequestSubjectsQuery extends BaseQuery
{

    public const NAME = 'supportRequestSubjects';
    public const PERMISSION = SupportRequestSubjectListPermission::KEY;

    public function __construct(protected SupportRequestSubjectService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    /**
     * @return array[]
     */
    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
            'query' => [
                'type' => Type::string(),
                'description' => 'Field to filter by "title" or "description" fields',
                'rules' => [
                    'nullable',
                    'string'
                ]
            ],
        ];
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return SupportRequestSubjectType::list();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return null|Collection
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?Collection {
        return $this->service->getList($args, $this->user());
    }
}

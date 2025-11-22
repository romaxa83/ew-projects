<?php


namespace App\GraphQL\Queries\Common\SupportRequests;


use App\GraphQL\Types\SupportRequests\SupportRequestType;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Permissions\SupportRequests\SupportRequestListPermission;
use App\Services\SupportRequests\SupportRequestService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseSupportRequestsQuery extends BaseQuery
{

    public const NAME = 'supportRequests';
    public const PERMISSION = SupportRequestListPermission::KEY;

    public function __construct(protected SupportRequestService $service)
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
            'date_from' => [
                'type' => Type::string(),
                'rules' => [
                    'nullable',
                    'date_format:Y-m-d',
                ]
            ],
            'date_to' => [
                'type' => Type::string(),
                'rules' => [
                    'nullable',
                    'date_format:Y-m-d',
                ]
            ],
            'subject_id' => [
                'type' => Type::id(),
                'rules' => [
                    'nullable',
                    Rule::exists(
                        SupportRequestSubject::class,
                        'id'
                    )
                        ->where('active', true)
                ]
            ],
            'closed' => [
                'type' => Type::boolean(),
            ],
            'page' => [
                'type' => Type::int(),
                'defaultValue' => 1
            ],
            'per_page' => [
                'type' => Type::int(),
                'defaultValue' => 10
            ]
        ];
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return SupportRequestType::paginate();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return LengthAwarePaginator
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->service->getList($args, $this->user());
    }
}

<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\Problems;

use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\Problem;
use App\Permissions\Dictionaries\DictionaryDeletePermission;
use App\Services\Dictionaries\ProblemService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class ProblemDeleteMutation extends BaseMutation
{
    public const NAME = 'problemDelete';
    public const PERMISSION = DictionaryDeletePermission::KEY;

    public function __construct(private ProblemService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Problem::class, 'id')
                ]
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return $this->service->delete(Problem::find($args['id']));
    }
}

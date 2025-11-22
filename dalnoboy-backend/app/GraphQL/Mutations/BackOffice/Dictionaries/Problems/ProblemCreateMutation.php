<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\Problems;

use App\Dto\Dictionaries\ProblemDto;
use App\GraphQL\InputTypes\Dictionaries\ProblemInputType;
use App\GraphQL\Types\Dictionaries\ProblemType;
use App\Models\Dictionaries\Problem;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\ProblemService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ProblemCreateMutation extends BaseMutation
{
    public const NAME = 'problemCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

    public function __construct(private ProblemService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ProblemType::nonNullType();
    }

    public function args(): array
    {
        return [
            'problem' => [
                'type' => ProblemInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Problem
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Problem
    {
        return makeTransaction(
            fn() => $this->service->create(
                ProblemDto::byArgs($args['problem'])
            )
        );
    }
}

<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\OptionAnswer;

use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\OptionAnswer;
use App\Permissions\Commercial\Commissionings\Question\DeletePermission;
use App\Repositories\Commercial\Commissioning\OptionAnswerRepository;
use App\Services\Commercial\Commissioning\OptionAnswerService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class DeleteMutation extends BaseMutation
{
    public const NAME = 'commissioningOptionAnswerDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(
        protected OptionAnswerService $service,
        protected OptionAnswerRepository $repo,
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(
                $this->repo->getByFields(['id' => $args['id']])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'int', Rule::exists(OptionAnswer::TABLE, 'id')],
        ];
    }
}



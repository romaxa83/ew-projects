<?php

namespace Core\GraphQL\Mutations;

use App\GraphQL\Types\NonNullType;
use App\Models\BaseModel;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseToggleActiveMutation extends BaseMutation
{
    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): BaseModel
    {
        $model = $this->model()::find($args['id']);

        $model->{$this->activeField()} = !$model->{$this->activeField()};

        $model->save();

        return $model;
    }

    abstract protected function model(): BaseModel|string;

    protected function activeField(): string
    {
        return 'active';
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists($this->model(), 'id')],
        ];
    }
}
